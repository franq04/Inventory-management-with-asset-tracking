<?php

namespace App\Http\Controllers\Custodian;

use App\Http\Controllers\Controller;
use App\Models\AssetMovement;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Employee;
use App\Models\IcsRecord;
use App\Models\InspectionReportItem;
use App\Models\Notification;
use App\Models\ParRecord;
use App\Models\PhysicalLocation;
use App\Models\PqsRecord;
use App\Models\Status;
use App\Models\StatusHistory;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InventoryAssignmentController extends Controller
{
    public function index(): View
    {
        $categories = Category::parentsWithChildren();
        $locations = PhysicalLocation::query()
            ->where('is_active', true)
            ->where('location_type', '!=', 'storage')
            ->orderBy('location_name')
            ->get()
            ->map(fn (PhysicalLocation $location) => $this->formatLocationOption($location))
            ->values();

        // Aggregate stats for the dashboard cards
        $totalAccepted = InspectionReportItem::query()
            ->where('quantity_accepted', '>', 0)
            ->whereIn('inspection_status_id', [Status::ITEM_ACCEPTED, Status::ITEM_RECORDED])
            ->count();

        $readyForPqs = InspectionReportItem::query()
            ->where('quantity_accepted', '>', 0)
            ->whereIn('inspection_status_id', [Status::ITEM_ACCEPTED, Status::ITEM_RECORDED])
            ->whereNull('property_no')
            ->count();

        $recordedInPqs = InspectionReportItem::query()
            ->where('quantity_accepted', '>', 0)
            ->whereIn('inspection_status_id', [Status::ITEM_ACCEPTED, Status::ITEM_RECORDED])
            ->whereNotNull('property_no')
            ->count();

        $stats = [
            'total' => $totalAccepted,
            'readyForPqs' => $readyForPqs,
            'recordedInPqs' => $recordedInPqs,
        ];

        return view('custodian.inventory.index', [
            'categories' => $categories,
            'locations' => $locations,
            'stats' => $stats,
        ]);
    }

    public function storeLocation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'location_name' => ['required', 'string', 'max:255'],
            'location_type' => ['nullable', Rule::in(['building', 'floor', 'room', 'other'])],
            'parent_location_id' => ['nullable', Rule::exists('physical_locations', 'location_id')->where(fn ($query) => $query->where('is_active', true))],
        ]);

        $normalizedName = preg_replace('/\s+/', ' ', trim((string) $validated['location_name']));
        $locationType = (string) ($validated['location_type'] ?? 'other');
        $parentLocationId = (int) ($validated['parent_location_id'] ?? 0) ?: null;

        $existingQuery = PhysicalLocation::query()
            ->where('is_active', true)
            ->whereRaw('LOWER(TRIM(location_name)) = ?', [mb_strtolower($normalizedName, 'UTF-8')]);

        if ($parentLocationId) {
            $existingQuery->where('parent_location_id', $parentLocationId);
        } else {
            $existingQuery->whereNull('parent_location_id');
        }

        $location = $existingQuery->first();

        if ($location && $location->location_type === 'storage') {
            return response()->json([
                'status' => 'error',
                'message' => 'Storage locations are reserved for turnover only.',
            ], 422);
        }

        if (! $location) {
            $location = PhysicalLocation::query()->create([
                'location_name' => $normalizedName,
                'location_type' => in_array($locationType, ['building', 'floor', 'room', 'other'], true) ? $locationType : 'other',
                'parent_location_id' => $parentLocationId,
                'is_active' => true,
                'description' => 'Added from inventory assignment form.',
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Location added successfully.',
            'data' => [
                'location' => $this->formatLocationOption($location),
            ],
        ]);
    }

    public function items(Request $request): JsonResponse
    {
        $search = trim((string) $request->input('search', ''));
        $state = strtolower((string) $request->input('state', 'all'));
        $categoryId = $request->input('category_id');
        $subCategoryId = $request->input('sub_category_id');
        [$dateFrom, $dateTo] = $this->resolveDateRange($request, fromQuery: false);

        $query = InspectionReportItem::with([
            'purchaseOrderItem.purchaseOrder.purchaseRequest',
            'purchaseOrderItem.purchaseOrder.purchaseRequest.requester.employee',
            'purchaseOrderItem.purchaseOrder.supplier',
            'report',
            'status',
            'propertyRecord.category.parent',
            'propertyRecord.accountableOfficer',
        ])->where('quantity_accepted', '>', 0)
            ->whereIn('inspection_status_id', [Status::ITEM_ACCEPTED, Status::ITEM_RECORDED]);

        if ($state === 'pending') {
            $query->whereNull('property_no');
        } elseif ($state === 'recorded') {
            $query->whereNotNull('property_no');
        }

        $this->applyInventorySearchFilter($query, $search);

        $this->applyReportDateFilter($query, $dateFrom, $dateTo);

        if ($categoryId) {
            $query->whereHas('propertyRecord.category', function ($catQuery) use ($categoryId) {
                $catQuery->where('parent_id', $categoryId);
            });
        }

        if ($subCategoryId) {
            $query->whereHas('propertyRecord', function ($pqsQuery) use ($subCategoryId) {
                $pqsQuery->where('cat_id', $subCategoryId);
            });
        }

        $items = $query->orderByDesc('ia_item_id')->get()->map(function (InspectionReportItem $item) {
            return $this->transformItem($item);
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'items' => $items,
            ],
        ]);
    }

    public function printPdf(Request $request): View
    {
        $state = strtolower((string) $request->query('state', 'all'));
        [$dateFrom, $dateTo] = $this->resolveDateRange($request, fromQuery: true);
        $generatedDateLabel = $this->buildGeneratedDateLabel($dateFrom, $dateTo);
        [$preparedByName, $preparedByRole] = $this->resolvePreparedBy();

        return view('custodian.inventory.print', [
            'items' => $this->inventoryExportItems($request),
            'state' => $state,
            'search' => trim((string) $request->query('search', '')),
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'generatedDateLabel' => $generatedDateLabel,
            'preparedByName' => $preparedByName,
            'preparedByRole' => $preparedByRole,
        ]);
    }

    public function exportExcel(Request $request): Response
    {
        [$dateFrom, $dateTo] = $this->resolveDateRange($request, fromQuery: true);
        $generatedDateLabel = $this->buildGeneratedDateLabel($dateFrom, $dateTo);
        [$preparedByName, $preparedByRole] = $this->resolvePreparedBy();

        $html = view('custodian.inventory.excel', [
            'items' => $this->inventoryExportItems($request),
            'state' => strtolower((string) $request->query('state', 'all')),
            'search' => trim((string) $request->query('search', '')),
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'generatedDateLabel' => $generatedDateLabel,
            'preparedByName' => $preparedByName,
            'preparedByRole' => $preparedByRole,
        ])->render();

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="Inventory-Assignment-'.date('Y-m-d').'.xls"');
    }

    private function inventoryExportItems(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $state = strtolower((string) $request->query('state', 'all'));
        [$dateFrom, $dateTo] = $this->resolveDateRange($request, fromQuery: true);

        $query = InspectionReportItem::with([
            'purchaseOrderItem.purchaseOrder.purchaseRequest',
            'purchaseOrderItem.purchaseOrder.purchaseRequest.requester.employee',
            'purchaseOrderItem.purchaseOrder.supplier',
            'report',
            'status',
            'propertyRecord.category.parent',
            'propertyRecord.accountableOfficer',
        ])->where('quantity_accepted', '>', 0)
            ->whereIn('inspection_status_id', [Status::ITEM_ACCEPTED, Status::ITEM_RECORDED]);

        if ($state === 'pending') {
            $query->whereNull('property_no');
        } elseif ($state === 'recorded') {
            $query->whereNotNull('property_no');
        }

        $this->applyInventorySearchFilter($query, $search);

        $this->applyReportDateFilter($query, $dateFrom, $dateTo);

        return $query->orderByDesc('ia_item_id')
            ->get()
            ->map(fn (InspectionReportItem $item) => $this->transformItem($item));
    }

    private function resolveDateRange(Request $request, bool $fromQuery = false): array
    {
        $dateFromRaw = trim((string) ($fromQuery ? $request->query('date_from', '') : $request->input('date_from', '')));
        $dateToRaw = trim((string) ($fromQuery ? $request->query('date_to', '') : $request->input('date_to', '')));

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

    private function applyReportDateFilter($query, ?string $dateFrom, ?string $dateTo): void
    {
        if (! $dateFrom || ! $dateTo) {
            return;
        }

        $query->whereHas('report', function ($reportQuery) use ($dateFrom, $dateTo) {
            $reportQuery->where(function ($dateQuery) use ($dateFrom, $dateTo) {
                $dateQuery->whereBetween('accepted_date', [$dateFrom, $dateTo])
                    ->orWhere(function ($fallbackQuery) use ($dateFrom, $dateTo) {
                        $fallbackQuery->whereNull('accepted_date')
                            ->whereBetween('inspection_date', [$dateFrom, $dateTo]);
                    });
            });
        });
    }

    private function buildGeneratedDateLabel(?string $dateFrom, ?string $dateTo): string
    {
        if ($dateFrom && $dateTo) {
            return Carbon::parse($dateFrom)->format('F d, Y').' - '.Carbon::parse($dateTo)->format('F d, Y');
        }

        return now()->format('F d, Y');
    }

    private function resolvePreparedBy(): array
    {
        $account = Auth::user();

        $preparedByName = $account?->employee?->full_name
            ?: $account?->username
            ?: 'System User';

        $preparedByRole = $account?->role
            ? ucfirst(str_replace('_', ' ', (string) $account->role))
            : 'User';

        return [$preparedByName, $preparedByRole];
    }

    private function applyInventorySearchFilter($query, string $search): void
    {
        if ($search === '') {
            return;
        }

        $query->where(function ($inner) use ($search) {
            $inner->where('property_no', 'like', "%{$search}%")
                ->orWhereHas('purchaseOrderItem', function ($poQuery) use ($search) {
                    $poQuery->where('item_description', 'like', "%{$search}%")
                        ->orWhere('unit', 'like', "%{$search}%");
                })->orWhereHas('report', function ($reportQuery) use ($search) {
                    $reportQuery->where('ia_no', 'like', "%{$search}%")
                        ->orWhere('po_no', 'like', "%{$search}%")
                        ->orWhere('invoice_no', 'like', "%{$search}%")
                        ->orWhere('remarks', 'like', "%{$search}%");
                })->orWhereHas('propertyRecord', function ($propertyQuery) use ($search) {
                    $propertyQuery->where('property_no', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('serial_number', 'like', "%{$search}%")
                        ->orWhere('article', 'like', "%{$search}%")
                        ->orWhereHas('category', function ($catQuery) use ($search) {
                            $catQuery->where('cat_name', 'like', "%{$search}%")
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
                        });
                })->orWhereHas('purchaseOrderItem.purchaseOrder.supplier', function ($supplierQuery) use ($search) {
                    $supplierQuery->where('supplier_name', 'like', "%{$search}%");
                })->orWhereHas('purchaseOrderItem.purchaseOrder.purchaseRequest', function ($prQuery) use ($search) {
                    $prQuery->where('pr_no', 'like', "%{$search}%")
                        ->orWhereHas('requester.employee', function ($employeeQuery) use ($search) {
                            $employeeQuery->where('first_name', 'like', "%{$search}%")
                                ->orWhere('middle_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('employee_id', 'like', "%{$search}%")
                                ->orWhereHas('section', function ($sectionQuery) use ($search) {
                                    $sectionQuery->where('section_name', 'like', "%{$search}%");
                                })
                                ->orWhereHas('position', function ($positionQuery) use ($search) {
                                    $positionQuery->where('position_title', 'like', "%{$search}%");
                                });
                        });
                });
        });
    }

    public function show(InspectionReportItem $inspectionReportItem): JsonResponse
    {
        $inspectionReportItem->loadMissing([
            'purchaseOrderItem.purchaseOrder.purchaseRequest.requester.employee',
            'purchaseOrderItem.purchaseOrder.supplier',
            'report',
            'status',
            'propertyRecord.category.parent',
            'propertyRecord.icsRecord',
            'propertyRecord.parRecord',
            'propertyRecord.accountableOfficer',
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $this->transformItem($inspectionReportItem, includeAssociations: true),
        ]);
    }

    public function store(Request $request, InspectionReportItem $inspectionReportItem): JsonResponse
    {
        $inspectionReportItem->loadMissing([
            'purchaseOrderItem.purchaseOrder.purchaseRequest.requester.employee',
            'report',
        ]);

        if ($inspectionReportItem->property_no) {
            return response()->json([
                'status' => 'error',
                'message' => 'This inspection item already has an associated property record.',
            ], 422);
        }

        $maxQuantity = max(1, (int) ($inspectionReportItem->quantity_accepted ?: $inspectionReportItem->quantity_delivered ?: 1));

        $validator = Validator::make($request->all(), [
            'category_id' => ['required', 'exists:categories,cat_id'],
            'sub_category_id' => ['required', 'exists:categories,cat_id'],
            'property_description' => ['required', 'string'],
            'unit' => ['required', 'string', 'max:100'],
            'quantity' => ['required', 'integer', 'min:1', 'max:'.$maxQuantity],
            'unit_cost' => ['required', 'numeric', 'min:0'],
            'date_acquired' => ['required', 'date'],
            'estimated_useful_life_value' => ['nullable', 'numeric', 'min:0.1'],
            'estimated_useful_life_unit' => ['nullable', Rule::in(['months', 'years'])],
            'estimated_useful_life' => ['nullable', 'string', 'max:100'],
            'initial_location_id' => ['nullable', Rule::exists('physical_locations', 'location_id')->where(fn ($query) => $query->where('is_active', true)->where('location_type', '!=', 'storage'))],
            'initial_location_name' => ['nullable', 'string', 'max:255'],
            'initial_location_type' => ['nullable', Rule::in(['building', 'floor', 'room', 'other'])],
            'initial_location_parent_id' => ['nullable', Rule::exists('physical_locations', 'location_id')->where(fn ($query) => $query->where('is_active', true))],
        ]);

        $validator->after(function ($validator) use ($inspectionReportItem) {
            $data = $validator->getData();
            $quantity = (int) ($data['quantity'] ?? 0);
            $serials = $this->normalizeSerialNumbers($inspectionReportItem->serial_numbers ?? null);

            if ($serials && $quantity > 0 && count($serials) > $quantity) {
                $validator->errors()->add('serial_numbers', 'Serial numbers recorded during inspection exceed the quantity to be recorded.');
            }

            if ($serials) {
                $duplicateSerials = array_unique(array_diff_assoc($serials, array_unique($serials)));
                if ($duplicateSerials) {
                    $validator->errors()->add('serial_numbers', 'Serial numbers must be unique. Duplicates found: '.implode(', ', $duplicateSerials));
                }

                $existingSerials = PqsRecord::whereIn('serial_number', $serials)->pluck('serial_number')->all();
                if ($existingSerials) {
                    $validator->errors()->add('serial_numbers', 'Serial numbers already recorded: '.implode(', ', array_unique($existingSerials)).'.');
                }
            }
        });

        $validated = $validator->validate();

        $estimatedUsefulLife = $this->normalizeUsefulLife(
            $validated['estimated_useful_life_value'] ?? null,
            $validated['estimated_useful_life_unit'] ?? null,
            $validated['estimated_useful_life'] ?? null
        );

        $parentCategory = Category::with('children')->findOrFail($validated['category_id']);
        $subCategory = $parentCategory->children->firstWhere('cat_id', $validated['sub_category_id']);

        if (! $subCategory) {
            return response()->json([
                'status' => 'error',
                'message' => 'Selected sub-category does not belong to the chosen category.',
            ], 422);
        }

        $quantity = (int) $validated['quantity'];
        $unitCost = (float) $validated['unit_cost'];
        $totalCost = round($quantity * $unitCost, 2);

        if ($totalCost <= 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Total cost must be greater than zero.',
            ], 422);
        }

        $serialNumbers = $this->normalizeSerialNumbers($inspectionReportItem->serial_numbers ?? null);

        if (count($serialNumbers) < $quantity) {
            $serialNumbers = array_pad($serialNumbers, $quantity, null);
        } elseif (count($serialNumbers) > $quantity) {
            $serialNumbers = array_slice($serialNumbers, 0, $quantity);
        }

        $account = Auth::user();
        $purchaseRequest = $inspectionReportItem->purchaseOrderItem?->purchaseOrder?->purchaseRequest;
        $accountableOfficer = $purchaseRequest?->requester?->employee;
        $accountableOfficer?->loadMissing('section');
        $accountableOfficerId = $accountableOfficer?->employee_id;
        $assignedSectionId = $accountableOfficer?->section?->section_id;
        $assignedDivisionId = $accountableOfficer?->section?->division_id;
        $requestedLocationId = (int) ($validated['initial_location_id'] ?? 0) ?: null;
        $requestedLocationName = trim((string) ($validated['initial_location_name'] ?? ''));
        $requestedLocationType = (string) ($validated['initial_location_type'] ?? 'other');
        $requestedLocationParentId = (int) ($validated['initial_location_parent_id'] ?? 0) ?: null;

        $initialLocation = $this->resolveInitialLocation(
            $requestedLocationId,
            $requestedLocationName,
            $requestedLocationType,
            $requestedLocationParentId,
            $assignedDivisionId,
            $assignedSectionId
        );
        if (! $initialLocation) {
            $suggestedLocationId = $this->suggestLocationForEmployee($accountableOfficer);
            $initialLocation = $suggestedLocationId ? PhysicalLocation::query()->find($suggestedLocationId) : null;
        }
        $initialLocationId = $initialLocation?->location_id;

        $dateAcquired = Carbon::parse($validated['date_acquired']);
        $year = (int) $dateAcquired->format('Y');

        $result = DB::transaction(function () use ($inspectionReportItem, $parentCategory, $subCategory, $validated, $quantity, $unitCost, $totalCost, $account, $serialNumbers, $accountableOfficerId, $assignedSectionId, $assignedDivisionId, $initialLocationId, $year, $estimatedUsefulLife) {
            $reservedPropertyNumbers = [];
            $createdPropertyNumbers = [];

            for ($index = 0; $index < $quantity; $index++) {
                $propertyNo = $this->generatePropertyNumber($parentCategory->cat_id, $subCategory->cat_id, $year, $reservedPropertyNumbers);
                $reservedPropertyNumbers[] = $propertyNo;
                $serialNumber = $serialNumbers[$index] ?? null;

                $description = $validated['property_description'];
                if ($serialNumber) {
                    $description .= ' (SN: '.$serialNumber.')';
                }

                $remarksParts = [];
                if ($serialNumber) {
                    $remarksParts[] = 'Serial No: '.$serialNumber;
                }
                $remarksParts[] = 'Recorded via Inspection '.$inspectionReportItem->ia_no;
                $remarks = implode(' | ', $remarksParts);

                PqsRecord::create([
                    'property_no' => $propertyNo,
                    'article' => $validated['property_description'],
                    'description' => $description,
                    'serial_number' => $serialNumber,
                    'date_acquired' => $validated['date_acquired'],
                    'unit_value' => $unitCost,
                    'unit' => $validated['unit'],
                    'on_hand_per_count' => 1,
                    'total_value' => $unitCost,
                    'remarks' => $remarks,
                    'accountable_officer_id' => $accountableOfficerId,
                    'cat_id' => $subCategory->cat_id,
                    'current_location_id' => $initialLocationId,
                    'current_custodian_employee_id' => $accountableOfficerId,
                    'assigned_division_id' => $assignedDivisionId,
                    'assigned_section_id' => $assignedSectionId,
                    'asset_status' => PqsRecord::STATUS_ACTIVE,
                    'last_movement_at' => now(),
                ]);

                AssetMovement::create([
                    'property_no' => $propertyNo,
                    'to_location_id' => $initialLocationId,
                    'to_custodian_employee_id' => $accountableOfficerId,
                    'to_division_id' => $assignedDivisionId,
                    'to_section_id' => $assignedSectionId,
                    'movement_type' => 'initial_assignment',
                    'reason_code' => 'inspection_recording',
                    'effective_at' => now(),
                    'recorded_by' => $account?->account_id,
                    'source_table' => 'inspection_acceptance',
                    'source_record_id' => $inspectionReportItem->ia_no,
                    'remarks' => 'Initial assignment during PQS recording.',
                ]);

                if ($unitCost < 50000) {
                    IcsRecord::create([
                        'property_no' => $propertyNo,
                        'description' => $description,
                        'quantity' => 1,
                        'unit' => $validated['unit'],
                        'unit_cost' => $unitCost,
                        'total_cost' => $unitCost,
                        'estimated_useful_life' => $estimatedUsefulLife,
                    ]);
                } else {
                    ParRecord::create([
                        'property_no' => $propertyNo,
                        'article_desc' => $validated['property_description'],
                        'quantity' => 1,
                        'unit' => $validated['unit'],
                        'date_acquired' => $validated['date_acquired'],
                        'unit_value' => $unitCost,
                        'amount' => $unitCost,
                        'estimated_useful_life' => $estimatedUsefulLife,
                    ]);
                }

                $createdPropertyNumbers[] = $propertyNo;
            }

            $propertyNumberList = implode(', ', $createdPropertyNumbers);
            $primaryPropertyNo = $createdPropertyNumbers[0] ?? null;

            $oldStatus = $inspectionReportItem->inspection_status_id;
            $newRemarks = trim(($inspectionReportItem->inspection_remarks ? $inspectionReportItem->inspection_remarks.' ' : '').'Recorded as '.$propertyNumberList);

            $inspectionReportItem->update([
                'property_no' => $primaryPropertyNo,
                'inspection_status_id' => Status::ITEM_RECORDED,
                'inspection_remarks' => $newRemarks,
            ]);

            $purchaseOrderItem = $inspectionReportItem->purchaseOrderItem;
            if ($purchaseOrderItem) {
                $purchaseOrderItem->update([
                    'inspection_status_id' => Status::ITEM_RECORDED,
                    'inspection_remarks' => trim(($purchaseOrderItem->inspection_remarks ? $purchaseOrderItem->inspection_remarks.' ' : '').'Recorded as '.$propertyNumberList),
                ]);
            }

            StatusHistory::create([
                'table_name' => 'inspection_acceptance',
                'record_id' => $inspectionReportItem->ia_no,
                'old_status_id' => $oldStatus,
                'new_status_id' => Status::ITEM_RECORDED,
                'changed_by' => $account?->account_id,
                'remarks' => 'Item recorded in PQS with property number(s) '.$propertyNumberList,
                'changed_at' => now(),
            ]);

            if ($account) {
                AuditLog::create([
                    'account_id' => $account->account_id,
                    'table_name' => 'pqs',
                    'action' => 'CREATE',
                    'description' => sprintf('Created PQS record(s) %s for inspection item %s', $propertyNumberList, $inspectionReportItem->ia_item_id),
                    'log_time' => now(),
                ]);
            }

            $purchaseRequest = $inspectionReportItem->purchaseOrderItem?->purchaseOrder?->purchaseRequest;
            if ($purchaseRequest && $purchaseRequest->account_id) {
                Notification::create([
                    'recipient_id' => $purchaseRequest->account_id,
                    'sender_id' => $account?->account_id,
                    'table_name' => 'inspection_acceptance',
                    'record_id' => $inspectionReportItem->ia_no,
                    'message' => sprintf('Item "%s" has been recorded in PQS with property number(s) %s.',
                        Str::limit($inspectionReportItem->purchaseOrderItem?->item_description ?? 'Item', 80),
                        $propertyNumberList
                    ),
                    'type' => 'success',
                    'is_read' => false,
                    'created_at' => now(),
                ]);
            }

            $inspectionReportItem->refresh();
            $inspectionReportItem->loadMissing(['propertyRecord.category.parent', 'propertyRecord.icsRecord', 'propertyRecord.parRecord', 'propertyRecord.accountableOfficer', 'status']);

            return [$inspectionReportItem, $createdPropertyNumbers];
        });

        [$updatedItem, $createdPropertyNumbers] = $result;

        $propertyNumberList = implode(', ', $createdPropertyNumbers);
        if ($propertyNumberList === '') {
            $message = 'PQS record created successfully.';
        } elseif (count($createdPropertyNumbers) > 1) {
            $message = 'PQS records created successfully with property numbers '.$propertyNumberList.'.';
        } else {
            $message = 'PQS record created successfully with property number '.$propertyNumberList.'.';
        }

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => [
                'item' => $this->transformItem($updatedItem, includeAssociations: true),
                'created_property_numbers' => $createdPropertyNumbers,
                'initial_location' => $this->formatLocationOption($initialLocation),
            ],
        ]);
    }

    protected function resolveInitialLocation(?int $locationId, ?string $locationName, string $locationType, ?int $parentLocationId, ?int $divisionId, ?int $sectionId): ?PhysicalLocation
    {
        $normalizedName = preg_replace('/\s+/', ' ', trim((string) $locationName));
        if ($normalizedName) {
            $existingQuery = PhysicalLocation::query()
                ->where('is_active', true)
                ->whereRaw('LOWER(TRIM(location_name)) = ?', [mb_strtolower($normalizedName, 'UTF-8')]);

            if ($parentLocationId) {
                $existingQuery->where('parent_location_id', $parentLocationId);
            } else {
                $existingQuery->whereNull('parent_location_id');
            }

            $existing = $existingQuery->first();

            if ($existing) {
                return $existing;
            }

            return PhysicalLocation::query()->create([
                'location_name' => $normalizedName,
                'location_type' => in_array($locationType, ['building', 'floor', 'room', 'other'], true) ? $locationType : 'other',
                'parent_location_id' => $parentLocationId,
                'division_id' => $divisionId,
                'section_id' => $sectionId,
                'description' => 'Auto-created from inventory assignment initial location input.',
                'is_active' => true,
            ]);
        }

        if (! $locationId) {
            return null;
        }

        return PhysicalLocation::query()
            ->where('is_active', true)
            ->where('location_type', '!=', 'storage')
            ->find($locationId);
    }

    protected function formatLocationOption(?PhysicalLocation $location): ?array
    {
        if (! $location) {
            return null;
        }

        $label = $this->buildLocationPathLabel($location);

        return [
            'id' => $location->location_id,
            'name' => $location->location_name,
            'label' => $label,
            'parent_id' => $location->parent_location_id,
            'location_type' => $location->location_type,
        ];
    }

    protected function buildLocationPathLabel(PhysicalLocation $location): string
    {
        $segments = [];
        $cursor = $location;
        $guard = 0;

        while ($cursor && $guard < 12) {
            $segment = trim((string) $cursor->location_name);
            if ($segment !== '') {
                $segments[] = $segment;
            }

            if (! $cursor->parent_location_id) {
                break;
            }

            $cursor = PhysicalLocation::query()->find($cursor->parent_location_id);
            $guard++;
        }

        $segments = array_reverse($segments);
        $label = implode(' / ', $segments);

        if ($location->location_code) {
            $label .= ' ('.$location->location_code.')';
        }

        return $label ?: $location->location_name;
    }

    protected function normalizeSerialNumbers($raw): array
    {
        $values = is_array($raw)
            ? $raw
            : preg_split('/[\r\n;,]+/', (string) $raw);

        return collect($values)
            ->map(fn ($value) => trim((string) $value))
            ->filter(fn ($value) => $value !== '')
            ->values()
            ->all();
    }

    protected function transformItem(InspectionReportItem $item, bool $includeAssociations = false): array
    {
        $poItem = $item->purchaseOrderItem;
        $po = $poItem?->purchaseOrder;
        $pr = $po?->purchaseRequest;
        $propertyRecord = $item->propertyRecord;
        $category = $propertyRecord?->category?->parent;
        $subCategory = $propertyRecord?->category;
        $accountableOfficer = $propertyRecord?->accountableOfficer ?? $pr?->requester?->employee;

        $unitCost = (float) ($poItem->unit_cost ?? 0);
        $quantity = (int) ($item->quantity_accepted ?: $poItem?->quantity ?? 0);
        $totalCost = round($quantity * $unitCost, 2);

        $base = [
            'ia_item_id' => $item->ia_item_id,
            'ia_no' => $item->ia_no,
            'po_no' => $po?->po_no,
            'pr_no' => $po?->pr_no,
            'item_description' => $poItem?->item_description,
            'unit' => $poItem?->unit,
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'total_cost' => $totalCost,
            'status_id' => $item->inspection_status_id,
            'status_name' => $item->status?->status_name,
            'property_no' => $item->property_no,
            'category' => $category?->cat_name,
            'category_id' => $category?->cat_id,
            'sub_category' => $subCategory?->cat_name,
            'sub_category_id' => $subCategory?->cat_id,
            'can_create' => $item->property_no === null,
            'supplier_name' => $po?->supplier?->supplier_name,
            'accountable_officer_id' => $accountableOfficer?->employee_id,
            'accountable_officer_name' => $accountableOfficer?->full_name,
            'inspection_serial_numbers' => $this->normalizeSerialNumbers($item->serial_numbers ?? null),
            'serial_number' => $propertyRecord?->serial_number,
        ];

        if (! $includeAssociations) {
            return $base;
        }

        $recommendedLocationId = $this->suggestLocationForEmployee($accountableOfficer);

        $base['recommended_initial_location_id'] = $recommendedLocationId;

        $base['property_record'] = $propertyRecord ? [
            'property_no' => $propertyRecord->property_no,
            'description' => $propertyRecord->description,
            'serial_number' => $propertyRecord->serial_number,
            'date_acquired' => optional($propertyRecord->date_acquired)->toDateString(),
            'unit' => $propertyRecord->unit,
            'unit_value' => (float) $propertyRecord->unit_value,
            'total_value' => (float) $propertyRecord->total_value,
            'quantity' => (int) $propertyRecord->on_hand_per_count,
            'estimated_useful_life' => $propertyRecord->icsRecord?->estimated_useful_life
                ?? $propertyRecord->parRecord?->estimated_useful_life,
            'remarks' => $propertyRecord->remarks,
            'accountable_officer_id' => $propertyRecord->accountable_officer_id,
            'accountable_officer_name' => $propertyRecord->accountableOfficer?->full_name,
            'current_location_id' => $propertyRecord->current_location_id,
        ] : null;

        if ($propertyRecord?->icsRecord) {
            $base['ics_record'] = [
                'ics_no' => $propertyRecord->icsRecord->ics_no,
                'description' => $propertyRecord->icsRecord->description,
                'quantity' => (int) $propertyRecord->icsRecord->quantity,
                'unit_cost' => (float) $propertyRecord->icsRecord->unit_cost,
                'total_cost' => (float) $propertyRecord->icsRecord->total_cost,
                'estimated_useful_life' => $propertyRecord->icsRecord->estimated_useful_life,
            ];
        }

        if ($propertyRecord?->parRecord) {
            $base['par_record'] = [
                'par_no' => $propertyRecord->parRecord->par_no,
                'article_desc' => $propertyRecord->parRecord->article_desc,
                'quantity' => (int) $propertyRecord->parRecord->quantity,
                'unit_value' => (float) $propertyRecord->parRecord->unit_value,
                'amount' => (float) $propertyRecord->parRecord->amount,
                'date_acquired' => optional($propertyRecord->parRecord->date_acquired)->toDateString(),
                'estimated_useful_life' => $propertyRecord->parRecord->estimated_useful_life,
            ];
        }

        $base['purchase_request_account_id'] = $pr?->account_id;

        return $base;
    }

    protected function suggestLocationForEmployee(?Employee $employee): ?int
    {
        if (! $employee) {
            return null;
        }

        $employee->loadMissing('section');
        $sectionId = $employee->section_id ?: $employee->section?->section_id;
        $divisionId = $employee->section?->division_id;

        $baseQuery = PhysicalLocation::query()
            ->where('is_active', true)
            ->where('location_type', '!=', 'storage')
            ->orderByRaw("CASE WHEN section_id IS NOT NULL THEN 0 WHEN division_id IS NOT NULL THEN 1 ELSE 2 END")
            ->orderByRaw("CASE location_type WHEN 'room' THEN 0 WHEN 'floor' THEN 1 WHEN 'building' THEN 2 ELSE 3 END")
            ->orderBy('location_name');

        if ($sectionId) {
            $sectionLocation = (clone $baseQuery)
                ->where('section_id', $sectionId)
                ->first();

            if ($sectionLocation) {
                return (int) $sectionLocation->location_id;
            }
        }

        if ($divisionId) {
            $divisionLocation = (clone $baseQuery)
                ->where('division_id', $divisionId)
                ->first();

            if ($divisionLocation) {
                return (int) $divisionLocation->location_id;
            }
        }

        return null;
    }

    protected function generatePropertyNumber(string $categoryId, string $subCategoryId, int $year, array $reservedNumbers = []): string
    {
        $prefix = sprintf('PQS-%s-%s-%d-', $categoryId, $subCategoryId, $year);

        $latest = PqsRecord::where('property_no', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('property_no')
            ->first();

        $sequence = 1;

        if ($latest) {
            $lastSequence = (int) Str::afterLast($latest->property_no, '-');
            $sequence = $lastSequence + 1;
        }

        do {
            $propertyNo = $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
            $sequence++;
        } while (in_array($propertyNo, $reservedNumbers, true)
            || PqsRecord::where('property_no', $propertyNo)->lockForUpdate()->exists());

        return $propertyNo;
    }

    private function normalizeUsefulLife($value, ?string $unit, ?string $legacy = null): ?string
    {
        if ($value !== null && $value !== '') {
            $numeric = (float) $value;
            if ($numeric <= 0) {
                return null;
            }

            $normalizedValue = rtrim(rtrim(number_format($numeric, 2, '.', ''), '0'), '.');
            $normalizedUnit = $unit === 'months' ? 'months' : 'years';

            return $normalizedValue.' '.$normalizedUnit;
        }

        $legacyValue = trim((string) $legacy);
        if ($legacyValue !== '') {
            return $legacyValue;
        }

        return null;
    }
}
