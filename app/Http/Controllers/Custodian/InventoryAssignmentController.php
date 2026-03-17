<?php

namespace App\Http\Controllers\Custodian;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\IcsRecord;
use App\Models\InspectionReportItem;
use App\Models\Notification;
use App\Models\ParRecord;
use App\Models\PqsRecord;
use App\Models\Status;
use App\Models\StatusHistory;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
            'stats' => $stats,
        ]);
    }

    public function items(Request $request): JsonResponse
    {
        $search = trim((string) $request->input('search', ''));
        $state = strtolower((string) $request->input('state', 'all'));
        $categoryId = $request->input('category_id');
        $subCategoryId = $request->input('sub_category_id');

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

        if ($search !== '') {
            $query->where(function ($inner) use ($search) {
                $inner->whereHas('purchaseOrderItem', function ($poQuery) use ($search) {
                    $poQuery->where('item_description', 'like', "%{$search}%");
                })->orWhereHas('report', function ($reportQuery) use ($search) {
                    $reportQuery->where('ia_no', 'like', "%{$search}%")
                        ->orWhere('po_no', 'like', "%{$search}%");
                });
            });
        }

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
            'estimated_useful_life' => ['nullable', 'string', 'max:100'],
            'serial_numbers' => ['nullable', 'array'],
            'serial_numbers.*' => ['nullable', 'string', 'max:255'],
        ]);

        $validator->after(function ($validator) {
            $data = $validator->getData();
            $quantity = (int) ($data['quantity'] ?? 0);
            $serials = $data['serial_numbers'] ?? [];

            if (! is_array($serials)) {
                return;
            }

            if (count($serials) > $quantity && $quantity > 0) {
                $validator->errors()->add('serial_numbers', 'Serial numbers provided exceed the quantity to be recorded.');
            }

            $trimmedSerials = array_filter(array_map(function ($value) {
                $trimmed = trim((string) $value);

                return $trimmed !== '' ? $trimmed : null;
            }, $serials));

            if ($trimmedSerials) {
                $duplicateSerials = array_unique(array_diff_assoc($trimmedSerials, array_unique($trimmedSerials)));
                if ($duplicateSerials) {
                    $validator->errors()->add('serial_numbers', 'Serial numbers must be unique. Duplicates found: '.implode(', ', $duplicateSerials));
                }

                $existingSerials = PqsRecord::whereIn('serial_number', $trimmedSerials)->pluck('serial_number')->all();
                if ($existingSerials) {
                    $validator->errors()->add('serial_numbers', 'Serial numbers already recorded: '.implode(', ', array_unique($existingSerials)).'.');
                }
            }
        });

        $validated = $validator->validate();

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

        $serialNumbers = array_map(function ($value) {
            $trimmed = trim((string) $value);

            return $trimmed !== '' ? $trimmed : null;
        }, $validated['serial_numbers'] ?? []);

        if (count($serialNumbers) < $quantity) {
            $serialNumbers = array_pad($serialNumbers, $quantity, null);
        } elseif (count($serialNumbers) > $quantity) {
            $serialNumbers = array_slice($serialNumbers, 0, $quantity);
        }

        $account = Auth::user();
        $purchaseRequest = $inspectionReportItem->purchaseOrderItem?->purchaseOrder?->purchaseRequest;
        $accountableOfficer = $purchaseRequest?->requester?->employee;
        $accountableOfficerId = $accountableOfficer?->employee_id;

        $dateAcquired = Carbon::parse($validated['date_acquired']);
        $year = (int) $dateAcquired->format('Y');

        $result = DB::transaction(function () use ($inspectionReportItem, $parentCategory, $subCategory, $validated, $quantity, $unitCost, $totalCost, $account, $serialNumbers, $accountableOfficerId, $year) {
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
                ]);

                if ($unitCost < 50000) {
                    IcsRecord::create([
                        'property_no' => $propertyNo,
                        'description' => $description,
                        'quantity' => 1,
                        'unit' => $validated['unit'],
                        'unit_cost' => $unitCost,
                        'total_cost' => $unitCost,
                        'estimated_useful_life' => $validated['estimated_useful_life'] ?? null,
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
            ],
        ]);
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
            'serial_number' => $propertyRecord?->serial_number,
        ];

        if (! $includeAssociations) {
            return $base;
        }

        $base['property_record'] = $propertyRecord ? [
            'property_no' => $propertyRecord->property_no,
            'description' => $propertyRecord->description,
            'serial_number' => $propertyRecord->serial_number,
            'date_acquired' => optional($propertyRecord->date_acquired)->toDateString(),
            'unit' => $propertyRecord->unit,
            'unit_value' => (float) $propertyRecord->unit_value,
            'total_value' => (float) $propertyRecord->total_value,
            'quantity' => (int) $propertyRecord->on_hand_per_count,
            'estimated_useful_life' => $propertyRecord->icsRecord?->estimated_useful_life,
            'remarks' => $propertyRecord->remarks,
            'accountable_officer_id' => $propertyRecord->accountable_officer_id,
            'accountable_officer_name' => $propertyRecord->accountableOfficer?->full_name,
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
            ];
        }

        $base['purchase_request_account_id'] = $pr?->account_id;

        return $base;
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
}
