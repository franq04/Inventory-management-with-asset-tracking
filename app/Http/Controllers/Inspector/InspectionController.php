<?php

namespace App\Http\Controllers\Inspector;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AuditLog;
use App\Models\InspectionReport;
use App\Models\InspectionReportItem;
use App\Models\Notification;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Status;
use App\Models\StatusHistory;
use App\Support\PurchaseRequestStatusSynchronizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Inspector Controller
 * 
 * Handles inspection workflow for delivered items.
 * Inspector (IAC) can:
 * - View items pending inspection
 * - Accept items (marks as ITEM_ACCEPTED - 302)
 * - Reject items with remarks (marks as ITEM_DEFECTIVE - 303)
 * 
 * After inspection, items proceed to Custodian for PQS creation.
 */
class InspectionController extends Controller
{
    /**
     * Display items pending inspection.
     */
    public function index(Request $request): View
    {
        if (Gate::denies('inspect-items')) {
            abort(403, 'Only inspectors can access this page.');
        }

        $tabs = [
            'pending' => [
                'label' => 'Pending Inspection',
                'statuses' => [Status::ITEM_PENDING_INSPECTION],
            ],
            'accepted' => [
                'label' => 'Accepted',
                'statuses' => [Status::ITEM_ACCEPTED],
            ],
            'rejected' => [
                'label' => 'Rejected/Defective',
                'statuses' => [Status::ITEM_DEFECTIVE],
            ],
            'recorded' => [
                'label' => 'Recorded in PQS',
                'statuses' => [Status::ITEM_RECORDED],
            ],
        ];

        $activeTab = $request->query('tab', 'pending');
        if (!array_key_exists($activeTab, $tabs)) {
            $activeTab = 'pending';
        }

        $statusIds = $tabs[$activeTab]['statuses'];

        // Get inspection items based on status
        $itemsQuery = InspectionReportItem::with([
            'report.purchaseOrder.purchaseRequest.requester.employee',
            'purchaseOrderItem',
            'status',
        ]);

        if ($activeTab === 'pending') {
            // Include items with no inspection record yet
            $itemsQuery->where(function ($query) use ($statusIds) {
                $query->whereIn('inspection_status_id', $statusIds)
                    ->orWhereNull('inspection_status_id');
            });
        } else {
            $itemsQuery->whereIn('inspection_status_id', $statusIds);
        }

        $items = $itemsQuery->orderByDesc('ia_item_id')->paginate(15)->withQueryString();

        // Count items per tab
        $tabCounts = [];
        foreach ($tabs as $key => $definition) {
            $countQuery = InspectionReportItem::query();
            if ($key === 'pending') {
                $countQuery->where(function ($q) use ($definition) {
                    $q->whereIn('inspection_status_id', $definition['statuses'])
                        ->orWhereNull('inspection_status_id');
                });
            } else {
                $countQuery->whereIn('inspection_status_id', $definition['statuses']);
            }
            $tabCounts[$key] = $countQuery->count();
        }

        return view('inspector.inspection.index', [
            'items' => $items,
            'activeTab' => $activeTab,
            'tabCounts' => $tabCounts,
            'tabs' => collect($tabs)->mapWithKeys(fn ($def, $key) => [$key => $def['label']])->toArray(),
        ]);
    }

    /**
     * Display inspection form for a specific purchase order.
     */
    public function form(PurchaseOrder $purchaseOrder): JsonResponse
    {
        if (Gate::denies('inspect-items')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Only inspectors can access inspection forms.',
            ], 403);
        }

        $purchaseOrder->load([
            'items',
            'supplier',
            'purchaseRequest.requester.employee',
            'inspectionReports.items.status',
        ]);

        $statuses = Status::where('status_scope', Status::SCOPE_PURCHASE_ORDER_ITEM)
            ->whereIn('status_id', [
                Status::ITEM_PENDING_INSPECTION,
                Status::ITEM_ACCEPTED,
                Status::ITEM_DEFECTIVE,
                Status::ITEM_RETURNED,
            ])
            ->get(['status_id', 'status_name']);

        $items = $purchaseOrder->items->map(function (PurchaseOrderItem $item) {
            $latestInspection = InspectionReportItem::where('po_item_id', $item->poi_id)
                ->orderByDesc('ia_item_id')
                ->first();

            return [
                'po_item_id' => $item->poi_id,
                'description' => $item->item_description,
                'quantity' => $item->quantity,
                'unit' => $item->unit,
                'unit_cost' => $item->unit_cost,
                'received_at' => $item->received_at?->toDateTimeString(),
                'current_status_id' => $latestInspection?->inspection_status_id ?? Status::ITEM_PENDING_INSPECTION,
                'quantity_accepted' => $latestInspection?->quantity_accepted ?? 0,
                'quantity_rejected' => $latestInspection?->quantity_rejected ?? 0,
                'inspection_remarks' => $latestInspection?->inspection_remarks,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'po_no' => $purchaseOrder->po_no,
                'pr_no' => $purchaseOrder->pr_no,
                'supplier' => $purchaseOrder->supplier?->supplier_name,
                'requester' => $purchaseOrder->purchaseRequest?->requester?->employee
                    ? collect([
                        $purchaseOrder->purchaseRequest->requester->employee->first_name,
                        $purchaseOrder->purchaseRequest->requester->employee->last_name,
                    ])->filter()->implode(' ')
                    : 'Unknown',
                'items' => $items,
                'statuses' => $statuses,
            ],
        ]);
    }

    /**
     * Accept an inspection item.
     * 
     * This action marks the item as ACCEPTED (302) so it can proceed to PQS creation.
     */
    public function acceptItem(Request $request, InspectionReportItem $inspectionReportItem): JsonResponse
    {
        if (Gate::denies('inspect-items')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Only inspectors can accept items.',
            ], 403);
        }

        $validated = $request->validate([
            'quantity_accepted' => 'required|integer|min:1',
            'warranty_expiration' => 'nullable|date',
            'remarks' => 'nullable|string|max:500',
        ]);

        $currentStatus = $inspectionReportItem->inspection_status_id;

        // Only pending items can be accepted
        if ($currentStatus && $currentStatus !== Status::ITEM_PENDING_INSPECTION) {
            throw ValidationException::withMessages([
                'status' => 'This item has already been inspected.',
            ]);
        }

        DB::transaction(function () use ($inspectionReportItem, $validated, $currentStatus) {
            $accountId = Auth::id();
            $now = now();

            // Update inspection item
            $inspectionReportItem->update([
                'inspection_status_id' => Status::ITEM_ACCEPTED,
                'quantity_accepted' => $validated['quantity_accepted'],
                'quantity_rejected' => 0,
                'inspection_remarks' => $validated['remarks'] ?? 'Item accepted during inspection.',
                'warranty_expiration' => $validated['warranty_expiration'] ?? null,
            ]);

            // Update the report
            $inspectionReportItem->report->update([
                'inspected_by' => $accountId,
                'accepted_by' => $accountId,
                'accepted_date' => $now->toDateString(),
            ]);

            // Record status history
            StatusHistory::create([
                'table_name' => 'inspection_acceptance',
                'record_id' => $inspectionReportItem->ia_no,
                'old_status_id' => $currentStatus ?? Status::ITEM_PENDING_INSPECTION,
                'new_status_id' => Status::ITEM_ACCEPTED,
                'changed_by' => $accountId,
                'remarks' => 'Item accepted during inspection.',
                'changed_at' => $now,
            ]);

            // Audit log
            AuditLog::create([
                'account_id' => $accountId,
                'table_name' => 'inspection_report_items',
                'action' => 'ACCEPT_ITEM',
                'description' => "Accepted item {$inspectionReportItem->ia_item_id} from inspection {$inspectionReportItem->ia_no}",
                'log_time' => $now,
            ]);

            // Notify custodians that item is ready for PQS
            $custodians = Account::where('role', 'custodian')->pluck('account_id');
            foreach ($custodians as $custodianId) {
                Notification::create([
                    'recipient_id' => $custodianId,
                    'sender_id' => $accountId,
                    'table_name' => 'inspection_acceptance',
                    'record_id' => $inspectionReportItem->ia_no,
                    'message' => "Item from inspection {$inspectionReportItem->ia_no} has been accepted and is ready for PQS recording.",
                    'type' => 'task',
                ]);
            }

            $inspectionReportItem->loadMissing(['report.purchaseOrder.items', 'report.purchaseOrder.inspectionReports.items']);
            $report = $inspectionReportItem->report;
            $purchaseOrder = $report?->purchaseOrder;

            if (! $purchaseOrder) {
                return;
            }

            $latestInspectionItems = collect();
            $purchaseOrder->inspectionReports
                ->flatMap(function (InspectionReport $inspectionReport) {
                    return $inspectionReport->items;
                })
                ->sortBy('ia_item_id')
                ->each(function (InspectionReportItem $item) use ($latestInspectionItems) {
                    $latestInspectionItems->put($item->po_item_id, $item);
                });

            $purchaseOrder->items->each(function (PurchaseOrderItem $item) use ($latestInspectionItems) {
                /** @var InspectionReportItem|null $latest */
                $latest = $latestInspectionItems->get($item->poi_id);

                if (! $latest) {
                    return;
                }

                $dirty = false;

                if ($item->inspection_status_id !== $latest->inspection_status_id) {
                    $item->inspection_status_id = $latest->inspection_status_id;
                    $dirty = true;
                }

                if ($item->inspection_remarks !== $latest->inspection_remarks) {
                    $item->inspection_remarks = $latest->inspection_remarks;
                    $dirty = true;
                }

                if ($dirty) {
                    $item->save();
                }
            });

            $latestStatuses = $latestInspectionItems->pluck('inspection_status_id')->filter()->values()->all();

            $normalizedStatuses = collect($latestStatuses)
                ->map(fn ($status) => (int) $status)
                ->map(fn ($status) => $status === Status::ITEM_RECORDED ? Status::ITEM_ACCEPTED : $status);

            $reportOverallStatus = Status::ITEM_PENDING_INSPECTION;
            if ($normalizedStatuses->isNotEmpty()) {
                if ($normalizedStatuses->every(fn ($status) => $status === Status::ITEM_ACCEPTED)) {
                    $reportOverallStatus = Status::ITEM_ACCEPTED;
                } elseif ($normalizedStatuses->contains(Status::ITEM_DEFECTIVE)) {
                    $reportOverallStatus = Status::ITEM_DEFECTIVE;
                } elseif ($normalizedStatuses->contains(Status::ITEM_RETURNED)) {
                    $reportOverallStatus = Status::ITEM_RETURNED;
                } elseif ($normalizedStatuses->contains(Status::ITEM_REPLACED)) {
                    $reportOverallStatus = Status::ITEM_REPLACED;
                }
            }

            if ($report && $report->overall_status_id !== $reportOverallStatus) {
                $previousReportStatus = $report->overall_status_id ?? Status::ITEM_PENDING_INSPECTION;
                $report->update([
                    'accepted_by' => $accountId,
                    'accepted_date' => $now->toDateString(),
                    'overall_status_id' => $reportOverallStatus,
                ]);

                StatusHistory::create([
                    'table_name' => 'inspection_acceptance',
                    'record_id' => $report->ia_no,
                    'old_status_id' => $previousReportStatus,
                    'new_status_id' => $reportOverallStatus,
                    'changed_by' => $accountId,
                    'remarks' => 'Inspection report reconciled after acceptance.',
                    'changed_at' => $now,
                ]);
            }

            $previousOrderStatus = $purchaseOrder->status_id;
            $orderStatus = Status::PO_DELIVERED_PENDING_INSPECTION;

            if ($normalizedStatuses->isEmpty()) {
                $orderStatus = Status::PO_DELIVERED_PENDING_INSPECTION;
            } elseif ($normalizedStatuses->contains(Status::ITEM_PENDING_INSPECTION)) {
                $orderStatus = Status::PO_DELIVERED_PENDING_INSPECTION;
            } elseif ($normalizedStatuses->every(fn ($status) => in_array($status, [Status::ITEM_ACCEPTED, Status::ITEM_RECORDED], true))) {
                $orderStatus = Status::PO_CLOSED;
            } else {
                $orderStatus = Status::PO_PARTIALLY_DELIVERED;
            }

            if ($previousOrderStatus !== $orderStatus) {
                $purchaseOrder->status_id = $orderStatus;
                $purchaseOrder->save();

                StatusHistory::create([
                    'table_name' => 'purchase_orders',
                    'record_id' => $purchaseOrder->po_no,
                    'old_status_id' => $previousOrderStatus,
                    'new_status_id' => $orderStatus,
                    'changed_by' => $accountId,
                    'remarks' => 'Inspection acceptance updated purchase order status.',
                    'changed_at' => $now,
                ]);

                PurchaseRequestStatusSynchronizer::sync(
                    $purchaseOrder,
                    $accountId,
                    sprintf('Inspection acceptance updated purchase order %s status.', $purchaseOrder->po_no)
                );
            }
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Item accepted successfully.',
            'data' => [
                'ia_item_id' => $inspectionReportItem->ia_item_id,
                'status_id' => Status::ITEM_ACCEPTED,
                'status_name' => 'Accepted',
            ],
        ]);
    }

    /**
     * Reject an inspection item.
     * 
     * This action marks the item as DEFECTIVE (303) with required remarks.
     */
    public function rejectItem(Request $request, InspectionReportItem $inspectionReportItem): JsonResponse
    {
        if (Gate::denies('inspect-items')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Only inspectors can reject items.',
            ], 403);
        }

        $validated = $request->validate([
            'quantity_rejected' => 'required|integer|min:1',
            'remarks' => 'required|string|max:1000',  // Remarks required for rejection
        ]);

        $currentStatus = $inspectionReportItem->inspection_status_id;

        if ($currentStatus && $currentStatus !== Status::ITEM_PENDING_INSPECTION) {
            throw ValidationException::withMessages([
                'status' => 'This item has already been inspected.',
            ]);
        }

        DB::transaction(function () use ($inspectionReportItem, $validated, $currentStatus) {
            $accountId = Auth::id();
            $now = now();

            $inspectionReportItem->update([
                'inspection_status_id' => Status::ITEM_DEFECTIVE,
                'quantity_accepted' => 0,
                'quantity_rejected' => $validated['quantity_rejected'],
                'inspection_remarks' => $validated['remarks'],
            ]);

            $inspectionReportItem->report->update([
                'inspected_by' => $accountId,
                'overall_status_id' => Status::ITEM_DEFECTIVE,
            ]);

            StatusHistory::create([
                'table_name' => 'inspection_acceptance',
                'record_id' => $inspectionReportItem->ia_no,
                'old_status_id' => $currentStatus ?? Status::ITEM_PENDING_INSPECTION,
                'new_status_id' => Status::ITEM_DEFECTIVE,
                'changed_by' => $accountId,
                'remarks' => "Item rejected: {$validated['remarks']}",
                'changed_at' => $now,
            ]);

            AuditLog::create([
                'account_id' => $accountId,
                'table_name' => 'inspection_report_items',
                'action' => 'REJECT_ITEM',
                'description' => "Rejected item {$inspectionReportItem->ia_item_id}: {$validated['remarks']}",
                'log_time' => $now,
            ]);

            // Notify the requester about rejected item
            $purchaseOrder = $inspectionReportItem->report->purchaseOrder;
            if ($purchaseOrder && $purchaseOrder->purchaseRequest) {
                Notification::create([
                    'recipient_id' => $purchaseOrder->purchaseRequest->account_id,
                    'sender_id' => $accountId,
                    'table_name' => 'inspection_acceptance',
                    'record_id' => $inspectionReportItem->ia_no,
                    'message' => "An item from your purchase request {$purchaseOrder->pr_no} was rejected during inspection.\nReason: {$validated['remarks']}",
                    'type' => 'warning',
                ]);
            }
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Item rejected with remarks.',
            'data' => [
                'ia_item_id' => $inspectionReportItem->ia_item_id,
                'status_id' => Status::ITEM_DEFECTIVE,
                'status_name' => 'Defective / Rejected',
            ],
        ]);
    }

    /**
     * Batch inspection - accept or reject multiple items at once.
     */
    public function batchInspect(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        if (Gate::denies('inspect-items')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Only inspectors can perform inspections.',
            ], 403);
        }

        $validated = $request->validate([
            'inspection_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.po_item_id' => 'required|exists:purchase_order_items,poi_id',
            'items.*.action' => 'required|in:accept,reject',
            'items.*.quantity_accepted' => 'required_if:items.*.action,accept|integer|min:0',
            'items.*.quantity_rejected' => 'required_if:items.*.action,reject|integer|min:0',
            'items.*.remarks' => 'required_if:items.*.action,reject|nullable|string|max:1000',
            'items.*.warranty_expiration' => 'nullable|date',
        ]);

        $results = DB::transaction(function () use ($purchaseOrder, $validated) {
            $accountId = Auth::id();
            $now = now();
            $results = [];

            // Get or create inspection report
            $report = InspectionReport::firstOrCreate(
                ['po_no' => $purchaseOrder->po_no, 'inspection_date' => $validated['inspection_date']],
                [
                    'ia_no' => $this->generateIaNumber(),
                    'fund_cluster' => $purchaseOrder->fund_cluster,
                    'inspected_by' => $accountId,
                    'overall_status_id' => Status::ITEM_PENDING_INSPECTION,
                ]
            );

            foreach ($validated['items'] as $itemData) {
                $poItemId = $itemData['po_item_id'];
                $action = $itemData['action'];

                // Get or create inspection item
                $inspectionItem = InspectionReportItem::firstOrCreate(
                    ['ia_no' => $report->ia_no, 'po_item_id' => $poItemId],
                    [
                        'quantity_delivered' => PurchaseOrderItem::find($poItemId)?->quantity ?? 1,
                        'inspection_status_id' => Status::ITEM_PENDING_INSPECTION,
                    ]
                );

                if ($action === 'accept') {
                    $inspectionItem->update([
                        'inspection_status_id' => Status::ITEM_ACCEPTED,
                        'quantity_accepted' => $itemData['quantity_accepted'],
                        'quantity_rejected' => 0,
                        'inspection_remarks' => $itemData['remarks'] ?? 'Accepted',
                        'warranty_expiration' => $itemData['warranty_expiration'] ?? null,
                    ]);
                } else {
                    $inspectionItem->update([
                        'inspection_status_id' => Status::ITEM_DEFECTIVE,
                        'quantity_accepted' => 0,
                        'quantity_rejected' => $itemData['quantity_rejected'],
                        'inspection_remarks' => $itemData['remarks'],
                    ]);
                }

                $results[] = [
                    'po_item_id' => $poItemId,
                    'action' => $action,
                    'status' => 'success',
                ];
            }

            // Update overall report status
            $report->load('items');
            $allAccepted = $report->items->every(fn ($i) => $i->inspection_status_id === Status::ITEM_ACCEPTED);
            $anyRejected = $report->items->contains(fn ($i) => $i->inspection_status_id === Status::ITEM_DEFECTIVE);

            $report->update([
                'accepted_by' => $accountId,
                'accepted_date' => $now->toDateString(),
                'overall_status_id' => $anyRejected ? Status::ITEM_DEFECTIVE : ($allAccepted ? Status::ITEM_ACCEPTED : Status::ITEM_PENDING_INSPECTION),
            ]);

            // Audit log
            AuditLog::create([
                'account_id' => $accountId,
                'table_name' => 'inspection_reports',
                'action' => 'BATCH_INSPECTION',
                'description' => "Batch inspection completed for PO {$purchaseOrder->po_no}, report {$report->ia_no}",
                'log_time' => $now,
            ]);

            return [
                'report' => $report,
                'items' => $results,
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Batch inspection completed.',
            'data' => $results,
        ]);
    }

    /**
     * Generate unique IA number.
     */
    protected function generateIaNumber(): string
    {
        $today = now()->format('Ymd');
        $prefix = "IA-{$today}-";

        $latest = InspectionReport::where('ia_no', 'like', "{$prefix}%")
            ->orderByDesc('ia_no')
            ->first();

        if ($latest) {
            $lastNum = (int) substr($latest->ia_no, -3);
            $nextNum = str_pad($lastNum + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $nextNum = '001';
        }

        return $prefix . $nextNum;
    }
}
