<?php

namespace App\Http\Controllers\Custodian;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AssetMovement;
use App\Models\AuditLog;
use App\Models\Division;
use App\Models\Employee;
use App\Models\Notification;
use App\Models\PhysicalLocation;
use App\Models\PqsRecord;
use App\Models\Section;
use App\Models\Status;
use App\Models\StatusHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AssetMovementController extends Controller
{
    public function processTurnover(Request $request): JsonResponse
    {
        $this->authorize('turnover', PqsRecord::class);

        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,employee_id'],
            'stockroom_location_id' => ['required', 'exists:physical_locations,location_id'],
            'effective_at' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $employee = Employee::query()->find($validated['employee_id']);

        if (! $employee) {
            throw ValidationException::withMessages([
                'employee_id' => 'The selected employee does not exist.',
            ]);
        }

        $location = PhysicalLocation::query()
            ->where('location_id', $validated['stockroom_location_id'])
            ->where('is_active', true)
            ->where('location_type', 'storage')
            ->first();

        if (! $location) {
            throw ValidationException::withMessages([
                'stockroom_location_id' => 'Select an active storage location for turnover.',
            ]);
        }

        $assets = PqsRecord::query()
            ->where(function ($query) use ($employee): void {
                $query->where('current_custodian_employee_id', $employee->employee_id)
                    ->orWhere('accountable_officer_id', $employee->employee_id);
            })
            ->orderBy('property_no')
            ->get();

        if ($assets->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No assets were found for the selected employee.',
                'data' => [
                    'summary' => [
                        'total_assets' => 0,
                        'eligible_assets' => 0,
                        'skipped_assets' => [],
                        'affected_assets' => [],
                        'failed_assets' => [],
                    ],
                ],
            ], 422);
        }

        $skippedAssets = $assets
            ->filter(fn (PqsRecord $asset): bool => ! $asset->canBeTurnedOver())
            ->values();

        if ($skippedAssets->isNotEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Some selected assets cannot be turned over because they are no longer transferable.',
                'data' => [
                    'summary' => [
                        'total_assets' => $assets->count(),
                        'eligible_assets' => $assets->count() - $skippedAssets->count(),
                        'skipped_assets' => $skippedAssets->map(fn (PqsRecord $asset): array => [
                            'property_no' => $asset->property_no,
                            'reason' => 'Asset is disposed or otherwise not transferable.',
                        ])->all(),
                        'affected_assets' => [],
                        'failed_assets' => [],
                    ],
                ],
            ], 422);
        }

        $effectiveAt = $validated['effective_at'] ?? now();
        $recordedBy = Auth::user()?->account_id;
        $affectedAssets = [];
        $batchReference = (string) Str::uuid();

        DB::transaction(function () use ($assets, $employee, $location, $validated, $effectiveAt, $recordedBy, $batchReference, &$affectedAssets): void {
            $assets = PqsRecord::query()
                ->whereIn('property_no', $assets->pluck('property_no'))
                ->lockForUpdate()
                ->orderBy('property_no')
                ->get();

            foreach ($assets as $asset) {
                $fromLocationId = $asset->current_location_id;
                $fromCustodianId = $asset->current_custodian_employee_id ?: $asset->accountable_officer_id;
                $targetDivisionId = $location->division_id ?? $asset->assigned_division_id;
                $targetSectionId = $location->section_id ?? $asset->assigned_section_id;

                $movement = AssetMovement::create([
                    'property_no' => $asset->property_no,
                    'from_location_id' => $fromLocationId,
                    'to_location_id' => $location->location_id,
                    'from_custodian_employee_id' => $fromCustodianId,
                    'to_custodian_employee_id' => null,
                    'from_division_id' => $asset->assigned_division_id,
                    'to_division_id' => $targetDivisionId,
                    'from_section_id' => $asset->assigned_section_id,
                    'to_section_id' => $targetSectionId,
                    'movement_type' => 'transfer',
                    'reason_code' => 'employee_turnover',
                    'effective_at' => $effectiveAt,
                    'recorded_by' => $recordedBy,
                    'source_table' => 'pqs_turnover_batch',
                    'source_record_id' => $batchReference,
                    'remarks' => $validated['remarks'] ?? null,
                ]);

                $asset->update([
                    'current_location_id' => $location->location_id,
                    'current_custodian_employee_id' => null,
                    'accountable_officer_id' => null,
                    'assigned_division_id' => $targetDivisionId,
                    'assigned_section_id' => $targetSectionId,
                    'asset_status' => PqsRecord::STATUS_TRANSFERRED,
                    'last_movement_at' => $effectiveAt,
                ]);

                $affectedAssets[] = [
                    'property_no' => $asset->property_no,
                    'movement_id' => $movement->movement_id,
                ];
            }

            $recipientIds = Account::query()
                ->where('role', 'custodian')
                ->pluck('account_id')
                ->unique()
                ->values();

            foreach ($recipientIds as $recipientId) {
                Notification::create([
                    'recipient_id' => (int) $recipientId,
                    'sender_id' => $recordedBy,
                    'table_name' => 'asset_movements',
                    'record_id' => $batchReference,
                    'message' => sprintf(
                        'Employee turnover batch %s moved %d asset(s) to %s.',
                        $batchReference,
                        $assets->count(),
                        $location->location_name
                    ),
                    'type' => 'task',
                    'is_read' => false,
                    'created_at' => now(),
                ]);
            }
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Employee asset turnover completed successfully.',
            'data' => [
                'summary' => [
                    'total_assets' => $assets->count(),
                    'eligible_assets' => $assets->count(),
                    'skipped_assets' => [],
                    'affected_assets' => $affectedAssets,
                    'failed_assets' => [],
                ],
                    'batch_reference' => $batchReference,
                'employee' => [
                    'employee_id' => $employee->employee_id,
                    'full_name' => $employee->full_name,
                ],
                'stockroom_location' => [
                    'location_id' => $location->location_id,
                    'location_name' => $location->location_name,
                    'location_code' => $location->location_code,
                ],
            ],
        ]);
    }

    public function transfer(Request $request, PqsRecord $pqsRecord): JsonResponse
    {
        $this->authorize('transfer', $pqsRecord);

        $validated = $request->validate([
            'to_location_id' => ['nullable', 'exists:physical_locations,location_id'],
            'to_custodian_employee_id' => ['nullable', 'exists:employees,employee_id'],
            'to_division_id' => ['nullable', 'exists:divisions,division_id'],
            'to_section_id' => ['nullable', 'exists:sections,section_id'],
            'movement_type' => ['required', 'in:transfer,relocation,inventory_correction,maintenance_out,maintenance_in'],
            'reason_code' => ['nullable', 'string', 'max:100'],
            'effective_at' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $account = Auth::user();

        $location = null;
        if (! empty($validated['to_location_id'])) {
            $location = PhysicalLocation::query()->find($validated['to_location_id']);
        }

        $targetLocationId = $validated['to_location_id'] ?? null;
        $targetCustodianId = $validated['to_custodian_employee_id'] ?? null;
        $targetDivisionId = $validated['to_division_id'] ?? $location?->division_id;
        $targetSectionId = $validated['to_section_id'] ?? $location?->section_id;

        if (! $targetDivisionId && ! $targetSectionId && ! $targetCustodianId && ! $targetLocationId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Provide at least one transfer target (location, custodian, division, or section).',
            ], 422);
        }

        if ($targetSectionId) {
            $section = DB::table('sections')
                ->where('section_id', $targetSectionId)
                ->first(['division_id']);

            if (! $section) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Selected section does not exist.',
                ], 422);
            }

            if ($targetDivisionId && (int) $section->division_id !== (int) $targetDivisionId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Selected section does not belong to the chosen division.',
                ], 422);
            }

            $targetDivisionId = (int) $section->division_id;
        }

        if (
            (int) ($pqsRecord->current_location_id ?? 0) === (int) ($targetLocationId ?? 0)
            && ($pqsRecord->current_custodian_employee_id ?? null) === ($targetCustodianId ?? $pqsRecord->current_custodian_employee_id)
            && (int) ($pqsRecord->assigned_division_id ?? 0) === (int) ($targetDivisionId ?? 0)
            && (int) ($pqsRecord->assigned_section_id ?? 0) === (int) ($targetSectionId ?? 0)
        ) {
            return response()->json([
                'status' => 'error',
                'message' => 'No movement detected. The target assignment matches current asset state.',
            ], 422);
        }

        if (in_array($pqsRecord->asset_status, [PqsRecord::STATUS_DISPOSED, PqsRecord::STATUS_LOST], true)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Disposed or lost assets cannot be transferred.',
            ], 422);
        }

        $movement = DB::transaction(function () use ($validated, $account, $pqsRecord, $targetLocationId, $targetCustodianId, $targetDivisionId, $targetSectionId): AssetMovement {
            $effectiveAt = $validated['effective_at'] ?? now();

            $movement = AssetMovement::create([
                'property_no' => $pqsRecord->property_no,
                'from_location_id' => $pqsRecord->current_location_id,
                'to_location_id' => $targetLocationId ?? $pqsRecord->current_location_id,
                'from_custodian_employee_id' => $pqsRecord->current_custodian_employee_id,
                'to_custodian_employee_id' => $targetCustodianId ?? $pqsRecord->current_custodian_employee_id,
                'from_division_id' => $pqsRecord->assigned_division_id,
                'to_division_id' => $targetDivisionId ?? $pqsRecord->assigned_division_id,
                'from_section_id' => $pqsRecord->assigned_section_id,
                'to_section_id' => $targetSectionId ?? $pqsRecord->assigned_section_id,
                'movement_type' => $validated['movement_type'],
                'reason_code' => $validated['reason_code'] ?? null,
                'effective_at' => $effectiveAt,
                'recorded_by' => $account?->account_id,
                'source_table' => 'pqs',
                'source_record_id' => $pqsRecord->property_no,
                'remarks' => $validated['remarks'] ?? null,
            ]);

            $assetStatus = $pqsRecord->asset_status ?: PqsRecord::STATUS_ACTIVE;
            if ($validated['movement_type'] === 'maintenance_out') {
                $assetStatus = PqsRecord::STATUS_FOR_REPAIR;
            }
            if ($validated['movement_type'] === 'maintenance_in') {
                $assetStatus = PqsRecord::STATUS_ACTIVE;
            }

            $pqsRecord->update([
                'current_location_id' => $movement->to_location_id,
                'current_custodian_employee_id' => $movement->to_custodian_employee_id,
                'assigned_division_id' => $movement->to_division_id,
                'assigned_section_id' => $movement->to_section_id,
                'asset_status' => $assetStatus,
                'last_movement_at' => $effectiveAt,
            ]);

            StatusHistory::create([
                'table_name' => 'asset_movements',
                'record_id' => (string) $movement->movement_id,
                'old_status_id' => Status::ITEM_RECORDED,
                'new_status_id' => Status::ITEM_RECORDED,
                'changed_by' => $account?->account_id,
                'remarks' => sprintf('Asset %s moved (%s).', $pqsRecord->property_no, $movement->movement_type),
                'changed_at' => now(),
            ]);

            AuditLog::create([
                'account_id' => $account?->account_id,
                'table_name' => 'asset_movements',
                'action' => 'TRANSFER',
                'description' => sprintf('Moved asset %s via movement %d', $pqsRecord->property_no, $movement->movement_id),
                'log_time' => now(),
            ]);

            if ($movement->to_custodian_employee_id) {
                $recipientAccountId = Employee::query()
                    ->where('employee_id', $movement->to_custodian_employee_id)
                    ->value('account_id');

                if ($recipientAccountId) {
                    Notification::create([
                        'recipient_id' => (int) $recipientAccountId,
                        'sender_id' => $account?->account_id,
                        'table_name' => 'asset_movements',
                        'record_id' => (string) $movement->movement_id,
                        'message' => sprintf('Asset %s has been assigned to your custody.', $pqsRecord->property_no),
                        'type' => 'task',
                        'is_read' => false,
                        'created_at' => now(),
                    ]);
                }
            }

            return $movement->fresh([
                'fromLocation',
                'toLocation',
                'fromCustodian',
                'toCustodian',
                'movedByAccount',
                'fromDivision',
                'toDivision',
                'fromSection',
                'toSection',
            ]);
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Asset movement has been recorded successfully.',
            'data' => [
                'movement' => $movement,
                'asset' => $pqsRecord->fresh(['currentLocation', 'currentCustodian', 'assignedDivision', 'assignedSection']),
            ],
        ]);
    }

    public function timeline(Request $request, PqsRecord $pqsRecord): JsonResponse
    {
        $this->authorize('view', $pqsRecord);

        $perPage = max(1, min((int) $request->input('per_page', 15), 50));

        $movements = AssetMovement::query()
            ->where('property_no', $pqsRecord->property_no)
            ->with([
                'fromLocation',
                'toLocation',
                'fromCustodian',
                'toCustodian',
                'movedByAccount',
                'fromDivision',
                'toDivision',
                'fromSection',
                'toSection',
            ])
            ->orderByDesc('effective_at')
            ->orderByDesc('movement_id')
            ->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => [
                'asset' => $pqsRecord->loadMissing(['currentLocation', 'currentCustodian', 'assignedDivision', 'assignedSection']),
                'movements' => $movements,
            ],
        ]);
    }

    public function reconciliationSummary(Request $request): JsonResponse
    {
        $this->authorize('viewAny', PqsRecord::class);

        $divisionId = $request->input('division_id');
        $sectionId = $request->input('section_id');
        $locationId = $request->input('location_id');
        $batchReference = $request->input('batch_reference');

        $assetsQuery = PqsRecord::query();

        if ($divisionId) {
            $assetsQuery->where('assigned_division_id', $divisionId);
        }

        if ($sectionId) {
            $assetsQuery->where('assigned_section_id', $sectionId);
        }

        if ($locationId) {
            $assetsQuery->where('current_location_id', $locationId);
        }

        $assets = (clone $assetsQuery);
        $totalAssets = (clone $assets)->count();
        $activeAssets = (clone $assets)->where('asset_status', PqsRecord::STATUS_ACTIVE)->count();
        $repairAssets = (clone $assets)->where('asset_status', PqsRecord::STATUS_FOR_REPAIR)->count();
        $unlocatedAssets = (clone $assets)->whereNull('current_location_id')->count();
        $staleAssets = (clone $assets)
            ->where(function ($query): void {
                $query->whereNull('last_inventory_date')
                    ->orWhere('last_inventory_date', '<', now()->subYear()->toDateString());
            })
            ->count();

        $byDivision = (clone $assets)
            ->select('assigned_division_id', DB::raw('COUNT(*) AS total'))
            ->groupBy('assigned_division_id')
            ->orderByDesc('total')
            ->with('assignedDivision')
            ->get()
            ->map(fn (PqsRecord $item) => [
                'division_id' => $item->assigned_division_id,
                'division_name' => $item->assignedDivision?->division_name,
                'total' => (int) $item->total,
            ]);

        $byLocation = (clone $assets)
            ->select('current_location_id', DB::raw('COUNT(*) AS total'))
            ->groupBy('current_location_id')
            ->orderByDesc('total')
            ->with('currentLocation')
            ->get()
            ->map(fn (PqsRecord $item) => [
                'location_id' => $item->current_location_id,
                'location_name' => $item->currentLocation?->location_name,
                'location_code' => $item->currentLocation?->location_code,
                'total' => (int) $item->total,
            ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'totals' => [
                    'assets' => $totalAssets,
                    'active' => $activeAssets,
                    'for_repair' => $repairAssets,
                    'unlocated' => $unlocatedAssets,
                    'stale_inventory' => $staleAssets,
                ],
                'breakdown' => [
                    'by_division' => $byDivision,
                    'by_location' => $byLocation,
                ],
            ],
        ]);
    }

    public function report(Request $request): View
    {
        $this->authorize('viewAny', PqsRecord::class);
        $this->validateReportFilters($request);

        $query = $this->buildMovementReportQuery($request);

        $movements = (clone $query)
            ->orderByDesc('effective_at')
            ->orderByDesc('movement_id')
            ->paginate(15)
            ->withQueryString();

        $statsQuery = clone $query;
        $stats = [
            'total' => (clone $statsQuery)->count(),
            'today' => (clone $statsQuery)->whereDate('effective_at', now()->toDateString())->count(),
            'transfer' => (clone $statsQuery)->where('movement_type', 'transfer')->count(),
            'maintenance' => (clone $statsQuery)->whereIn('movement_type', ['maintenance_out', 'maintenance_in'])->count(),
        ];

        $movementTypes = AssetMovement::query()
            ->select('movement_type')
            ->distinct()
            ->orderBy('movement_type')
            ->pluck('movement_type');

        $divisions = Division::query()
            ->orderBy('division_name')
            ->get(['division_id', 'division_name']);

        $sections = Section::query()
            ->orderBy('section_name')
            ->get(['section_id', 'section_name', 'division_id']);

        $locations = PhysicalLocation::query()
            ->where('is_active', true)
            ->orderBy('location_name')
            ->get(['location_id', 'location_name', 'division_id', 'section_id']);

        $custodians = Employee::query()
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get(['employee_id', 'first_name', 'middle_name', 'last_name', 'suffix']);

        return view('inventory.pqs.movements.index', [
            'movements' => $movements,
            'stats' => $stats,
            'movementTypes' => $movementTypes,
            'divisions' => $divisions,
            'sections' => $sections,
            'locations' => $locations,
            'custodians' => $custodians,
        ]);
    }

    public function printPdf(Request $request): View
    {
        $this->authorize('viewAny', PqsRecord::class);
        $this->validateReportFilters($request);

        $movements = $this->buildMovementReportQuery($request)
            ->orderByDesc('effective_at')
            ->orderByDesc('movement_id')
            ->get();

        return view('inventory.pqs.movements.print', [
            'movements' => $movements,
            'filters' => $request->only([
                'search',
                'movement_type',
                'division_id',
                'section_id',
                'location_id',
                'custodian_employee_id',
                'batch_reference',
                'date_from',
                'date_to',
            ]),
        ]);
    }

    public function exportExcel(Request $request): Response
    {
        $this->authorize('viewAny', PqsRecord::class);
        $this->validateReportFilters($request);

        $movements = $this->buildMovementReportQuery($request)
            ->orderByDesc('effective_at')
            ->orderByDesc('movement_id')
            ->get();

        $html = view('inventory.pqs.movements.excel', [
            'movements' => $movements,
        ])->render();

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="Asset-Movement-Report-' . date('Y-m-d') . '.xls"');
    }

    public function exportCsv(Request $request): Response
    {
        $this->authorize('viewAny', PqsRecord::class);
        $this->validateReportFilters($request);

        $movements = $this->buildMovementReportQuery($request)
            ->orderByDesc('effective_at')
            ->orderByDesc('movement_id')
            ->get();

        $stream = fopen('php://temp', 'r+');

        fputcsv($stream, [
            'Date',
            'Property No',
            'Article',
            'Movement Type',
            'From Location',
            'To Location',
            'From Custodian',
            'To Custodian',
            'Recorded By',
            'Reason',
            'Remarks',
        ]);

        foreach ($movements as $movement) {
            fputcsv($stream, [
                optional($movement->effective_at)->format('Y-m-d H:i:s'),
                $movement->property_no,
                $movement->property?->article,
                $movement->movement_type,
                $movement->fromLocation?->location_name,
                $movement->toLocation?->location_name,
                $movement->fromCustodian?->full_name,
                $movement->toCustodian?->full_name,
                $movement->movedByAccount?->username ?: 'System',
                $movement->reason_code,
                $movement->remarks,
            ]);
        }

        rewind($stream);
        $csv = stream_get_contents($stream) ?: '';
        fclose($stream);

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="Asset-Movement-Report-' . date('Y-m-d') . '.csv"');
    }

    protected function validateReportFilters(Request $request): array
    {
        $validator = Validator::make(
            $request->all(),
            [
                'search' => ['nullable', 'string', 'max:255'],
                'movement_type' => ['nullable', 'in:initial_assignment,transfer,relocation,inventory_correction,maintenance_out,maintenance_in,disposal,write_off'],
                'division_id' => ['nullable', 'exists:divisions,division_id'],
                'section_id' => ['nullable', 'exists:sections,section_id'],
                'location_id' => ['nullable', 'exists:physical_locations,location_id'],
                'custodian_employee_id' => ['nullable', 'exists:employees,employee_id'],
                'batch_reference' => ['nullable', 'string', 'max:100'],
                'date_from' => ['nullable', 'date'],
                'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            ],
            [
                'date_to.after_or_equal' => 'Date To must be on or after Date From.',
            ]
        );

        $validator->after(function ($validator) use ($request): void {
            $divisionId = $request->input('division_id');
            $sectionId = $request->input('section_id');
            $locationId = $request->input('location_id');

            if ($divisionId && $sectionId) {
                $sectionDivisionId = Section::query()
                    ->where('section_id', $sectionId)
                    ->value('division_id');

                if ($sectionDivisionId && (int) $sectionDivisionId !== (int) $divisionId) {
                    $validator->errors()->add('section_id', 'Selected section does not belong to the chosen division.');
                }
            }

            if ($locationId) {
                $location = PhysicalLocation::query()
                    ->where('location_id', $locationId)
                    ->first(['division_id', 'section_id']);

                if ($location && $divisionId && $location->division_id && (int) $location->division_id !== (int) $divisionId) {
                    $validator->errors()->add('location_id', 'Selected location does not belong to the chosen division.');
                }

                if ($location && $sectionId && $location->section_id && (int) $location->section_id !== (int) $sectionId) {
                    $validator->errors()->add('location_id', 'Selected location does not belong to the chosen section.');
                }
            }
        });

        return $validator->validate();
    }

    protected function buildMovementReportQuery(Request $request)
    {
        $query = AssetMovement::query()
            ->with([
                'property',
                'fromLocation',
                'toLocation',
                'fromCustodian',
                'toCustodian',
                'fromDivision',
                'toDivision',
                'fromSection',
                'toSection',
                'movedByAccount',
            ]);

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));

            $query->where(function ($builder) use ($search): void {
                $builder->where('property_no', 'like', "%{$search}%")
                    ->orWhere('remarks', 'like', "%{$search}%")
                    ->orWhere('reason_code', 'like', "%{$search}%")
                    ->orWhereHas('property', function ($propertyQuery) use ($search): void {
                        $propertyQuery->where('article', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('movement_type')) {
            $query->where('movement_type', $request->input('movement_type'));
        }

        if ($request->filled('division_id')) {
            $query->where('to_division_id', $request->input('division_id'));
        }

        if ($request->filled('section_id')) {
            $query->where('to_section_id', $request->input('section_id'));
        }

        if ($request->filled('location_id')) {
            $query->where('to_location_id', $request->input('location_id'));
        }

        if ($request->filled('custodian_employee_id')) {
            $query->where('to_custodian_employee_id', $request->input('custodian_employee_id'));
        }

        if ($request->filled('batch_reference')) {
            $query->where('source_record_id', trim((string) $request->input('batch_reference')));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('effective_at', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('effective_at', '<=', $request->date('date_to'));
        }

        return $query;
    }
}
