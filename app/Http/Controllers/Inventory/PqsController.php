<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Division;
use App\Models\Employee;
use App\Models\PhysicalLocation;
use App\Models\PqsRecord;
use App\Models\Section;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PqsController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $categoryFilter = $request->input('category');
        $assignmentFilter = $request->input('assignment');
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);

        $query = $this->buildFilteredPqsQuery($request);

        $pqsRecords = $query
            ->orderByDesc('date_acquired')
            ->orderByDesc('property_no')
            ->paginate(5)
            ->withQueryString();

        if ($request->ajax()) {
            $tableHtml = view('inventory.pqs.partials.records-table', [
                'records' => $pqsRecords,
            ])->render();

            return response()->json([
                'html' => $tableHtml,
                'total' => $pqsRecords->total(),
            ]);
        }

        $agg = DB::table('pqs')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(total_value) as total_value')
            ->selectRaw('SUM(CASE WHEN property_no IN (SELECT property_no FROM ics) THEN 1 ELSE 0 END) as with_ics')
            ->selectRaw('SUM(CASE WHEN property_no IN (SELECT property_no FROM par) THEN 1 ELSE 0 END) as with_par')
            ->selectRaw('SUM(CASE WHEN property_no NOT IN (SELECT property_no FROM ics) AND property_no NOT IN (SELECT property_no FROM par) THEN 1 ELSE 0 END) as unassigned')
            ->first();

        $stats = [
            'total' => (int) $agg->total,
            'withIcs' => (int) $agg->with_ics,
            'withPar' => (int) $agg->with_par,
            'unassigned' => (int) $agg->unassigned,
            'totalValue' => (float) $agg->total_value,
        ];

        $categories = Category::query()
            ->orderBy('cat_name')
            ->get(['cat_id', 'cat_name']);

        $recentAssets = PqsRecord::query()
            ->with(['category', 'currentLocation', 'currentCustodian'])
            ->orderByDesc('date_acquired')
            ->limit(5)
            ->get();

        $transferLocations = PhysicalLocation::query()
            ->where('is_active', true)
            ->orderBy('location_name')
            ->get(['location_id', 'location_name', 'location_code', 'location_type', 'division_id', 'section_id']);

        $turnoverLocations = PhysicalLocation::query()
            ->where('is_active', true)
            ->where('location_type', 'storage')
            ->orderBy('location_name')
            ->get(['location_id', 'location_name', 'location_code', 'location_type', 'division_id', 'section_id']);

        $transferCustodians = Employee::query()
            ->whereNotNull('account_id')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get(['employee_id', 'first_name', 'middle_name', 'last_name', 'suffix', 'section_id']);

        $turnoverEmployees = Employee::query()
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get(['employee_id', 'first_name', 'middle_name', 'last_name', 'suffix', 'section_id', 'account_id']);

        $transferDivisions = Division::query()
            ->orderBy('division_name')
            ->get(['division_id', 'division_name']);

        $transferSections = Section::query()
            ->orderBy('section_name')
            ->get(['section_id', 'section_name', 'division_id']);

        return view('inventory.pqs.index', [
            'records' => $pqsRecords,
            'stats' => $stats,
            'search' => $search,
            'categoryFilter' => $categoryFilter,
            'assignmentFilter' => $assignmentFilter,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'categories' => $categories,
            'recentAssets' => $recentAssets,
            'transferLocations' => $transferLocations,
            'turnoverLocations' => $turnoverLocations,
            'transferCustodians' => $transferCustodians,
            'turnoverEmployees' => $turnoverEmployees,
            'transferDivisions' => $transferDivisions,
            'transferSections' => $transferSections,
        ]);
    }

    public function show(PqsRecord $pqsRecord): JsonResponse
    {
        return $this->jsonRecord($pqsRecord);
    }

    public function showByProperty(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'property_no' => ['required', 'string'],
        ]);

        $record = PqsRecord::query()->findOrFail($validated['property_no']);

        return $this->jsonRecord($record);
    }

    protected function jsonRecord(PqsRecord $pqsRecord): JsonResponse
    {
        $pqsRecord->loadMissing([
            'category.parent',
            'accountableOfficer.position',
            'accountableOfficer.section.division',
            'currentLocation',
            'currentCustodian.position',
            'currentCustodian.section.division',
            'assignedDivision',
            'assignedSection',
            'icsRecord',
            'parRecord',
            'movements.fromLocation',
            'movements.toLocation',
            'movements.movedByAccount',
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $this->transformRecord($pqsRecord),
        ]);
    }

    protected function transformRecord(PqsRecord $record): array
    {
        $category = $record->category;
        $parentCategory = $category?->parent;
        $accountableOfficer = $record->accountableOfficer;
        $currentCustodian = $record->currentCustodian;
        $receivedBy = $currentCustodian ?: $accountableOfficer;
        $icsRecord = $record->icsRecord;
        $parRecord = $record->parRecord;
        $recentMovements = $record->movements
            ->sortByDesc('effective_at')
            ->take(10)
            ->values();

        $formatOffice = static function ($employee): ?string {
            if (! $employee) {
                return null;
            }

            $parts = [
                $employee->position?->position_title,
                $employee->section?->section_name,
                $employee->section?->division?->division_name,
            ];

            $parts = array_values(array_filter(array_map(static fn ($value) => trim((string) $value), $parts)));

            return $parts ? implode(' / ', $parts) : null;
        };

        $serialNumbers = collect(preg_split('/[\r\n;,]+/', (string) $record->serial_number))
            ->map(static fn ($value) => trim($value))
            ->filter(static fn ($value) => $value !== '')
            ->values()
            ->all();

        return [
            'property_no' => $record->property_no,
            'article' => $record->article,
            'description' => $record->description,
            'serial_numbers' => $serialNumbers,
            'date_acquired' => optional($record->date_acquired)->toDateString(),
            'unit' => $record->unit,
            'quantity' => $record->on_hand_per_count,
            'unit_value' => $record->unit_value !== null ? (float) $record->unit_value : null,
            'total_value' => $record->total_value !== null ? (float) $record->total_value : null,
            'remarks' => $record->remarks,
            'category' => $category?->cat_name,
            'category_parent' => $parentCategory?->cat_name,
            'accountable_officer' => $accountableOfficer ? [
                'id' => $accountableOfficer->employee_id,
                'name' => $accountableOfficer->full_name,
                'position' => $accountableOfficer->position?->position_title,
                'office' => $formatOffice($accountableOfficer),
            ] : null,
            'received_by' => $receivedBy ? [
                'id' => $receivedBy->employee_id,
                'name' => $receivedBy->full_name,
                'position' => $receivedBy->position?->position_title,
                'office' => $formatOffice($receivedBy),
                'signature' => $this->signatureDataUri($receivedBy->signature),
            ] : null,
            'current_location' => $record->currentLocation ? [
                'id' => $record->currentLocation->location_id,
                'name' => $record->currentLocation->location_name,
                'code' => $record->currentLocation->location_code,
                'type' => $record->currentLocation->location_type,
            ] : null,
            'current_custodian' => $currentCustodian ? [
                'id' => $currentCustodian->employee_id,
                'name' => $currentCustodian->full_name,
                'position' => $currentCustodian->position?->position_title,
                'office' => $formatOffice($currentCustodian),
            ] : null,
            'current_location_id' => $record->current_location_id,
            'current_custodian_employee_id' => $record->current_custodian_employee_id,
            'assigned_division_id' => $record->assigned_division_id,
            'assigned_section_id' => $record->assigned_section_id,
            'assigned_division' => $record->assignedDivision?->division_name,
            'assigned_section' => $record->assignedSection?->section_name,
            'asset_status' => $record->asset_status,
            'last_movement_at' => optional($record->last_movement_at)->toDateTimeString(),
            'last_inventory_date' => optional($record->last_inventory_date)->toDateString(),
            'ics_record' => $icsRecord ? [
                'ics_no' => $icsRecord->ics_no,
                'description' => $icsRecord->description,
                'quantity' => (int) $icsRecord->quantity,
                'unit_cost' => (float) $icsRecord->unit_cost,
                'total_cost' => (float) $icsRecord->total_cost,
                'estimated_useful_life' => $icsRecord->estimated_useful_life,
            ] : null,
            'par_record' => $parRecord ? [
                'par_no' => $parRecord->par_no,
                'article_desc' => $parRecord->article_desc,
                'quantity' => (int) $parRecord->quantity,
                'unit_value' => (float) $parRecord->unit_value,
                'amount' => (float) $parRecord->amount,
                'date_acquired' => optional($parRecord->date_acquired)->toDateString(),
            ] : null,
            'recent_movements' => $recentMovements->map(function ($movement) {
                return [
                    'movement_id' => $movement->movement_id,
                    'movement_type' => $movement->movement_type,
                    'from_location' => $movement->fromLocation?->location_name,
                    'to_location' => $movement->toLocation?->location_name,
                    'moved_by' => $movement->movedByAccount?->username,
                    'effective_at' => optional($movement->effective_at)->toDateTimeString(),
                    'remarks' => $movement->remarks,
                ];
            })->values()->all(),
        ];
    }

    protected function signatureDataUri($signature): ?string
    {
        if ($signature === null || $signature === '') {
            return null;
        }

        if (is_resource($signature)) {
            $signature = stream_get_contents($signature);
        }

        if (! is_string($signature) || $signature === '') {
            return null;
        }

        return 'data:image/png;base64,'.base64_encode($signature);
    }

    public function printPdf(Request $request): View
    {
        $search = trim((string) $request->input('search'));
        $categoryFilter = $request->input('category');
        $assignmentFilter = $request->input('assignment');
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);
        $generatedOnLabel = $this->buildGeneratedOnLabel($dateFrom, $dateTo);

        $query = $this->buildFilteredPqsQuery($request);

        $records = $query
            ->orderByDesc('date_acquired')
            ->orderByDesc('property_no')
            ->get();

        $account = Auth::user();
        $preparedByName = $account?->employee?->full_name
            ?: $account?->username
            ?: 'System User';
        $preparedByRole = $account?->role
            ? ucfirst(str_replace('_', ' ', (string) $account->role))
            : 'User';

        return view('inventory.pqs.print', [
            'records' => $records,
            'search' => $search,
            'categoryFilter' => $categoryFilter,
            'assignmentFilter' => $assignmentFilter,
            'generatedOnLabel' => $generatedOnLabel,
            'preparedByName' => $preparedByName,
            'preparedByRole' => $preparedByRole,
        ]);
    }

    public function exportExcel(Request $request): Response
    {
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);

        $query = $this->buildFilteredPqsQuery($request);

        $records = $query
            ->orderByDesc('date_acquired')
            ->orderByDesc('property_no')
            ->get();

        $html = view('inventory.pqs.excel', [
            'records' => $records,
            'generatedOnLabel' => $this->buildGeneratedOnLabel($dateFrom, $dateTo),
        ])->render();

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="PQS-Registry-' . date('Y-m-d') . '.xls"');
    }

    private function buildFilteredPqsQuery(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $categoryFilter = $request->input('category');
        $assignmentFilter = $request->input('assignment');
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);

        $query = PqsRecord::query()->with(['category.parent', 'accountableOfficer.section', 'accountableOfficer.position', 'currentLocation', 'currentCustodian.section', 'currentCustodian.position', 'icsRecord', 'parRecord']);

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('property_no', 'like', "%{$search}%")
                    ->orWhere('article', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhere('remarks', 'like', "%{$search}%")
                    ->orWhere('unit', 'like', "%{$search}%")
                    ->orWhere('asset_status', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($categoryQuery) use ($search) {
                        $categoryQuery->where('cat_name', 'like', "%{$search}%")
                            ->orWhereHas('parent', function ($parentQuery) use ($search) {
                                $parentQuery->where('cat_name', 'like', "%{$search}%");
                            });
                    })
                    ->orWhereHas('accountableOfficer', function ($officerQuery) use ($search) {
                        $officerQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('middle_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('employee_id', 'like', "%{$search}%")
                            ->orWhereHas('section', function ($sectionQuery) use ($search) {
                                $sectionQuery->where('section_name', 'like', "%{$search}%");
                            })
                            ->orWhereHas('position', function ($positionQuery) use ($search) {
                                $positionQuery->where('position_title', 'like', "%{$search}%");
                            });
                    })
                    ->orWhereHas('currentCustodian', function ($custodianQuery) use ($search) {
                        $custodianQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('middle_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('employee_id', 'like', "%{$search}%")
                            ->orWhereHas('section', function ($sectionQuery) use ($search) {
                                $sectionQuery->where('section_name', 'like', "%{$search}%");
                            })
                            ->orWhereHas('position', function ($positionQuery) use ($search) {
                                $positionQuery->where('position_title', 'like', "%{$search}%");
                            });
                    })
                    ->orWhereHas('currentLocation', function ($locationQuery) use ($search) {
                        $locationQuery->where('location_name', 'like', "%{$search}%")
                            ->orWhere('location_code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('icsRecord', function ($icsQuery) use ($search) {
                        $icsQuery->where('ics_no', 'like', "%{$search}%");
                    })
                    ->orWhereHas('parRecord', function ($parQuery) use ($search) {
                        $parQuery->where('par_no', 'like', "%{$search}%");
                    });
            });
        }

        if ($categoryFilter) {
            $query->where('cat_id', $categoryFilter);
        }

        if ($assignmentFilter === 'ics') {
            $query->whereHas('icsRecord');
        } elseif ($assignmentFilter === 'par') {
            $query->whereHas('parRecord');
        } elseif ($assignmentFilter === 'unassigned') {
            $query->whereDoesntHave('icsRecord')->whereDoesntHave('parRecord');
        }

        if ($dateFrom && $dateTo) {
            $query->whereBetween('date_acquired', [$dateFrom, $dateTo]);
        }

        return $query;
    }

    private function resolveDateRange(Request $request): array
    {
        $dateFromRaw = trim((string) $request->input('date_from', ''));
        $dateToRaw = trim((string) $request->input('date_to', ''));

        if ($dateFromRaw !== '' && $dateToRaw === '') {
            $dateToRaw = $dateFromRaw;
        }

        if ($dateToRaw !== '' && $dateFromRaw === '') {
            $dateFromRaw = $dateToRaw;
        }

        if ($dateFromRaw === '' || $dateToRaw === '') {
            return [null, null];
        }

        try {
            $dateFrom = Carbon::parse($dateFromRaw)->toDateString();
            $dateTo = Carbon::parse($dateToRaw)->toDateString();
        } catch (\Throwable $exception) {
            return [null, null];
        }

        if ($dateFrom > $dateTo) {
            [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
        }

        return [$dateFrom, $dateTo];
    }

    private function buildGeneratedOnLabel(?string $dateFrom, ?string $dateTo): string
    {
        if ($dateFrom && $dateTo) {
            return Carbon::parse($dateFrom)->format('F d, Y').' - '.Carbon::parse($dateTo)->format('F d, Y');
        }

        return now()->format('F d, Y');
    }
}
