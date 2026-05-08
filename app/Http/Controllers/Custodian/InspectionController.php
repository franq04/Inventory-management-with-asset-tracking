<?php

namespace App\Http\Controllers\Custodian;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\InspectionReport;
use App\Models\InspectionReportItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Status;
use App\Support\PurchaseRequestStatusSynchronizer;
use App\Models\StatusHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InspectionController extends Controller
{
    /**
     * Display grouped inspection results across status categories.
     */
    public function index(): View
    {
        $this->backfillPendingInspectionItems();

        $statusMap = [
            'pending' => [Status::ITEM_PENDING_INSPECTION],
            'accepted' => [Status::ITEM_ACCEPTED, Status::ITEM_RECORDED],
            'defective' => [Status::ITEM_DEFECTIVE],
            'returned' => [Status::ITEM_RETURNED],
        ];

        $itemsByStatus = [];

        $baseQuery = PurchaseOrderItem::query()
            ->with([
                'purchaseOrder.supplier',
                'latestInspectionItem.report',
                'latestInspectionItem.status',
            ])
            ->whereHas('purchaseOrder');

        foreach ($statusMap as $key => $statusIds) {
            $statusIds = array_map('intval', (array) $statusIds);

            $query = (clone $baseQuery)->where(function ($builder) use ($statusIds) {
                $builder->whereHas('latestInspectionItem', function ($inspectionQuery) use ($statusIds) {
                    $inspectionQuery->whereIn('inspection_status_id', $statusIds);
                });

                if (in_array(Status::ITEM_PENDING_INSPECTION, $statusIds, true)) {
                    $builder->orWhereDoesntHave('latestInspectionItem');
                }
            });

            $paginator = $query
                ->orderByDesc('poi_id')
                ->paginate(5, ['*'], $key.'_page')
                ->withQueryString();

            $itemsByStatus[$key] = $paginator;
        }

        $allItems = (clone $baseQuery)
            ->orderByDesc('poi_id')
            ->paginate(5, ['*'], 'all_page')
            ->withQueryString();

        return view('custodian.inspection.index', [
            'itemsByStatus' => $itemsByStatus,
            'allItems' => $allItems,
        ]);
    }

    public function printPdf(Request $request): View
    {
        [$items, $activeTab] = $this->inspectionExportPayload($request);

        return view('custodian.inspection.print', [
            'items' => $items,
            'activeTab' => $activeTab,
        ]);
    }

    public function exportExcel(Request $request): Response
    {
        [$items] = $this->inspectionExportPayload($request);

        $html = view('custodian.inspection.excel', [
            'items' => $items,
        ])->render();

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="Inspection-Items-'.date('Y-m-d').'.xls"');
    }

    private function inspectionExportPayload(Request $request): array
    {
        $statusMap = [
            'pending' => [Status::ITEM_PENDING_INSPECTION],
            'accepted' => [Status::ITEM_ACCEPTED, Status::ITEM_RECORDED],
            'defective' => [Status::ITEM_DEFECTIVE],
            'returned' => [Status::ITEM_RETURNED],
        ];

        $activeTab = strtolower((string) $request->query('tab', 'all'));
        if ($activeTab !== 'all' && ! array_key_exists($activeTab, $statusMap)) {
            $activeTab = 'all';
        }

        $baseQuery = PurchaseOrderItem::query()
            ->with([
                'purchaseOrder.supplier',
                'latestInspectionItem.report',
                'latestInspectionItem.status',
            ])
            ->whereHas('purchaseOrder');

        if ($activeTab !== 'all') {
            $statusIds = array_map('intval', (array) ($statusMap[$activeTab] ?? []));

            $baseQuery->where(function ($builder) use ($statusIds) {
                $builder->whereHas('latestInspectionItem', function ($inspectionQuery) use ($statusIds) {
                    $inspectionQuery->whereIn('inspection_status_id', $statusIds);
                });

                if (in_array(Status::ITEM_PENDING_INSPECTION, $statusIds, true)) {
                    $builder->orWhereDoesntHave('latestInspectionItem');
                }
            });
        }

        return [
            $baseQuery->orderByDesc('poi_id')->get(),
            $activeTab,
        ];
    }

    /**
     * Fetch purchase order data and existing inspection information for the modal form.
     */
    public function form(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        if (! $this->userCanInspect()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Only Inspection & Acceptance Committee members may access inspection records.',
            ], 403);
        }

        $this->ensurePendingInspectionForOrder($purchaseOrder);

        $purchaseOrder->load([
            'items',
            'supplier',
            'inspectionReports.items.status',
            'inspectionReports.inspector.employee',
        ]);

        $selectedIaNo = $request->string('report')->toString();
        $reports = $purchaseOrder->inspectionReports->sortByDesc('inspection_date')->values();
        $activeReport = $reports->firstWhere(fn (InspectionReport $report) => $report->ia_no === $selectedIaNo)
            ?? $reports->first();

        $latestInspectionItems = $this->collectLatestInspectionItems($purchaseOrder);

        $items = $purchaseOrder->items->map(function (PurchaseOrderItem $item) use ($activeReport, $latestInspectionItems) {
            $batchInspection = $activeReport?->items->firstWhere('po_item_id', $item->poi_id);
            $latestInspection = $latestInspectionItems->get($item->poi_id);

            $batchLooksLikeDefaultPending = $batchInspection
                && (int) ($batchInspection->inspection_status_id ?? 0) === Status::ITEM_PENDING_INSPECTION
                && (int) ($batchInspection->quantity_accepted ?? 0) === 0
                && (int) ($batchInspection->quantity_rejected ?? 0) === 0
                && blank($batchInspection->inspection_remarks)
                && blank($batchInspection->warranty_expiration);

            $preferredInspection = ($batchInspection && ! $batchLooksLikeDefaultPending)
                ? $batchInspection
                : ($latestInspection ?? $batchInspection);

            $delivered = $preferredInspection?->quantity_delivered
                ?? $item->quantity;

            return [
                'po_item_id' => $item->poi_id,
                'item_description' => $item->item_description,
                'quantity' => $delivered,
                'unit' => $item->unit,
                'status_id' => $preferredInspection?->inspection_status_id,
                'quantity_accepted' => $preferredInspection?->quantity_accepted ?? 0,
                'quantity_rejected' => $preferredInspection?->quantity_rejected ?? 0,
                'remarks' => $preferredInspection?->inspection_remarks,
                'warranty_expiration' => optional($preferredInspection?->warranty_expiration)->toDateString(),
            ];
        })->values();

        $statuses = Status::query()
            ->where('status_scope', Status::SCOPE_PURCHASE_ORDER_ITEM)
            ->whereIn('status_id', [
                Status::ITEM_PENDING_INSPECTION,
                Status::ITEM_ACCEPTED,
                Status::ITEM_DEFECTIVE,
                Status::ITEM_RETURNED,
                Status::ITEM_REPLACED,
                Status::ITEM_RECORDED,
            ])
            ->orderBy('status_code')
            ->get(['status_id', 'status_name', 'status_code']);

        $currentInspectorName = $this->accountDisplayName(Auth::user());

        return response()->json([
            'status' => 'success',
            'data' => [
                'po' => [
                    'po_no' => $purchaseOrder->po_no,
                    'pr_no' => $purchaseOrder->pr_no,
                    'supplier' => $purchaseOrder->supplier?->supplier_name,
                    'order_date' => optional($purchaseOrder->order_date)->toDateString(),
                    'delivery_date' => optional($purchaseOrder->delivery_date)->toDateString(),
                    'fund_cluster' => $purchaseOrder->fund_cluster,
                    'remarks' => $purchaseOrder->remarks,
                ],
                'reports' => $reports->map(function (InspectionReport $report) {
                    return [
                        'ia_no' => $report->ia_no,
                        'inspection_date' => optional($report->inspection_date)->toDateString(),
                        'accepted_date' => optional($report->accepted_date)->toDateString(),
                        'invoice_no' => $report->invoice_no,
                        'status_name' => $report->status?->status_name,
                        'items_count' => $report->items->count(),
                    ];
                })->values(),
                'active_report' => $activeReport ? [
                    'ia_no' => $activeReport->ia_no,
                    'inspection_date' => optional($activeReport->inspection_date)->toDateString(),
                    'accepted_date' => optional($activeReport->accepted_date)->toDateString(),
                    'invoice_no' => $activeReport->invoice_no,
                    'invoice_date' => optional($activeReport->invoice_date)->toDateString(),
                    'remarks' => $activeReport->remarks,
                    'overall_status_id' => $activeReport->overall_status_id,
                    'inspected_by' => $activeReport->inspected_by,
                    'inspected_by_name' => $this->resolveReportInspectorName($activeReport),
                ] : null,
                'items' => $items,
                'statuses' => $statuses,
                'current_inspector_name' => $currentInspectorName,
            ],
        ]);
    }

    /**
     * Persist inspection and acceptance results for a purchase order.
     */
    public function store(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        if (! $this->userCanInspect()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Only Inspection & Acceptance Committee members may record inspection results.',
            ], 403);
        }

        $validated = $request->validate([
            'ia_no' => 'nullable|string',
            'inspection_date' => 'required|date',
            'accepted_date' => 'nullable|date',
            'invoice_no' => 'nullable|string|max:100',
            'invoice_date' => 'nullable|date',
            'remarks' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.po_item_id' => 'required|exists:purchase_order_items,poi_id',
            'items.*.status_id' => 'required|exists:statuses,status_id',
            'items.*.quantity_accepted' => 'nullable|integer|min:0',
            'items.*.quantity_rejected' => 'nullable|integer|min:0',
            'items.*.remarks' => 'nullable|string',
            'items.*.warranty_expiration' => 'nullable|date',
        ]);

        $account = Auth::user();

        $savedReport = DB::transaction(function () use ($validated, $purchaseOrder, $account) {
            $purchaseOrder->loadMissing(['items', 'inspectionReports.items']);

            $report = null;
            $previousOverallStatus = Status::ITEM_PENDING_INSPECTION;

            if (! empty($validated['ia_no'])) {
                $report = InspectionReport::where('ia_no', $validated['ia_no'])
                    ->where('po_no', $purchaseOrder->po_no)
                    ->lockForUpdate()
                    ->first();

                if (! $report) {
                    throw ValidationException::withMessages([
                        'ia_no' => 'The selected inspection record was not found for this purchase order.',
                    ]);
                }

                $previousOverallStatus = $report->overall_status_id ?? $previousOverallStatus;
            }

            if (! $report) {
                $report = new InspectionReport([
                    'ia_no' => $this->generateIaNumber(),
                    'po_no' => $purchaseOrder->po_no,
                ]);
            }

            $report->fill([
                'fund_cluster' => $purchaseOrder->fund_cluster,
                'inspection_date' => $validated['inspection_date'],
                'accepted_date' => $validated['accepted_date'] ?? null,
                'invoice_no' => $validated['invoice_no'] ?? null,
                'invoice_date' => $validated['invoice_date'] ?? null,
                'inspected_by' => $account?->account_id,
                'accepted_by' => $account?->account_id,
                'remarks' => $validated['remarks'] ?? null,
            ]);

            if (! $report->overall_status_id) {
                $report->overall_status_id = Status::ITEM_PENDING_INSPECTION;
            }

            $report->save();

            $itemStatuses = [];

            foreach ($validated['items'] as $itemData) {
                /** @var PurchaseOrderItem|null $poItem */
                $poItem = $purchaseOrder->items->firstWhere('poi_id', $itemData['po_item_id']);
                if (! $poItem) {
                    continue;
                }

                $deliveredQuantity = $itemData['quantity'] ?? $poItem->quantity;
                $accepted = (int) ($itemData['quantity_accepted'] ?? 0);
                $rejected = (int) ($itemData['quantity_rejected'] ?? 0);

                if ($accepted + $rejected > $deliveredQuantity) {
                    $rejected = max(0, $deliveredQuantity - $accepted);
                }

                InspectionReportItem::updateOrCreate(
                    [
                        'ia_no' => $report->ia_no,
                        'po_item_id' => $poItem->poi_id,
                    ],
                    [
                        'quantity_delivered' => $deliveredQuantity,
                        'quantity_accepted' => $accepted,
                        'quantity_rejected' => $rejected,
                        'inspection_status_id' => $itemData['status_id'],
                        'inspection_remarks' => $itemData['remarks'] ?? null,
                        'warranty_expiration' => $itemData['warranty_expiration'] ?? null,
                    ]
                );

                $itemStatuses[] = $itemData['status_id'];
            }

            $reportOverallStatus = $this->determineOverallStatus($itemStatuses);
            $report->overall_status_id = $reportOverallStatus;
            $report->save();

            $purchaseOrder->refresh()->load(['items', 'inspectionReports.items']);

            $latestInspectionItems = $this->collectLatestInspectionItems($purchaseOrder);
            $latestStatuses = $latestInspectionItems->pluck('inspection_status_id')->filter()->values()->all();

            $previousOrderStatus = $purchaseOrder->status_id;
            $orderStatus = $this->mapItemStatusesToOrderStatus($latestStatuses);

            if ($previousOrderStatus !== $orderStatus) {
                $purchaseOrder->status_id = $orderStatus;
                $purchaseOrder->save();

                StatusHistory::create([
                    'table_name' => 'purchase_orders',
                    'record_id' => $purchaseOrder->po_no,
                    'old_status_id' => $previousOrderStatus,
                    'new_status_id' => $orderStatus,
                    'changed_by' => $account?->account_id,
                    'remarks' => 'Inspection and acceptance update applied.',
                    'changed_at' => now(),
                ]);

                PurchaseRequestStatusSynchronizer::sync(
                    $purchaseOrder,
                    $account?->account_id,
                    sprintf('Inspection results updated purchase order %s status.', $purchaseOrder->po_no)
                );
            }

            if ($previousOverallStatus !== $reportOverallStatus) {
                StatusHistory::create([
                    'table_name' => 'inspection_acceptance',
                    'record_id' => $report->ia_no,
                    'old_status_id' => $previousOverallStatus,
                    'new_status_id' => $reportOverallStatus,
                    'changed_by' => $account?->account_id,
                    'remarks' => 'Inspection results recorded.',
                    'changed_at' => now(),
                ]);
            }

            $this->syncPurchaseOrderInspectionState($purchaseOrder, $latestInspectionItems);

            AuditLog::create([
                'account_id' => $account?->account_id,
                'table_name' => 'inspection_reports',
                'action' => $report->wasRecentlyCreated ? 'CREATE' : 'UPDATE',
                'description' => sprintf('Inspection report %s saved for PO %s', $report->ia_no, $purchaseOrder->po_no),
            ]);

            return $report;
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Inspection results saved successfully.',
            'data' => [
                'ia_no' => $savedReport->ia_no,
            ],
        ]);
    }

    protected function userCanInspect(): bool
    {
        $user = Auth::user();

        return $user && in_array($user->role, ['iac', 'admin'], true);
    }

    protected function backfillPendingInspectionItems(): void
    {
        $orders = PurchaseOrder::query()
            ->where('status_id', Status::PO_DELIVERED_PENDING_INSPECTION)
            ->get(['po_no']);

        foreach ($orders as $order) {
            $this->ensurePendingInspectionForOrder($order);
        }
    }

    protected function ensurePendingInspectionForOrder(PurchaseOrder $purchaseOrder): void
    {
        DB::transaction(function () use ($purchaseOrder) {
            /** @var PurchaseOrder|null $order */
            $order = PurchaseOrder::with([
                'items' => function ($query) {
                    $query->whereNotNull('received_at')->with('latestInspectionItem');
                },
                'inspectionReports.items',
            ])->lockForUpdate()->find($purchaseOrder->po_no);

            if (! $order) {
                return;
            }

            $itemsNeedingInspection = $order->items->filter(function (PurchaseOrderItem $item) {
                return $item->received_at && $item->latestInspectionItem === null;
            });

            if ($itemsNeedingInspection->isEmpty()) {
                return;
            }

            $pendingReport = $order->inspectionReports->first(function (InspectionReport $report) {
                return (int) $report->overall_status_id === Status::ITEM_PENDING_INSPECTION;
            });

            if (! $pendingReport) {
                $pendingReport = InspectionReport::create([
                    'ia_no' => $this->generateIaNumber(),
                    'po_no' => $order->po_no,
                    'fund_cluster' => $order->fund_cluster,
                    'overall_status_id' => Status::ITEM_PENDING_INSPECTION,
                ]);

                $order->inspectionReports->push($pendingReport);
            }

            foreach ($itemsNeedingInspection as $item) {
                InspectionReportItem::firstOrCreate(
                    [
                        'ia_no' => $pendingReport->ia_no,
                        'po_item_id' => $item->poi_id,
                    ],
                    [
                        'quantity_delivered' => $item->quantity,
                        'quantity_accepted' => 0,
                        'quantity_rejected' => 0,
                        'inspection_status_id' => Status::ITEM_PENDING_INSPECTION,
                        'inspection_remarks' => null,
                    ]
                );
            }
        });
    }

    protected function collectLatestInspectionItems(PurchaseOrder $purchaseOrder): Collection
    {
        $latest = collect();

        $purchaseOrder->inspectionReports
            ->flatMap(function (InspectionReport $report) {
                return $report->items;
            })
            ->sortBy('ia_item_id')
            ->each(function (InspectionReportItem $item) use ($latest) {
                $latest->put($item->po_item_id, $item);
            });

        return $latest;
    }

    protected function syncPurchaseOrderInspectionState(PurchaseOrder $purchaseOrder, Collection $latestInspectionItems): void
    {
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
    }

    protected function generateIaNumber(): string
    {
        $datePart = now()->format('Ymd');
        $prefix = 'IA-' . $datePart . '-';

        $last = InspectionReport::where('ia_no', 'like', $prefix . '%')
            ->orderByDesc('ia_no')
            ->first();

        $sequence = 1;
        if ($last) {
            $sequence = (int) Str::afterLast($last->ia_no, '-') + 1;
        }

        return $prefix . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
    }

    protected function determineOverallStatus(array $itemStatuses): int
    {
        if (empty($itemStatuses)) {
            return Status::ITEM_PENDING_INSPECTION;
        }

        $normalizedStatuses = collect($itemStatuses)
            ->filter(fn ($status) => $status !== null)
            ->map(function ($status) {
                $status = (int) $status;
                return $status === Status::ITEM_RECORDED ? Status::ITEM_ACCEPTED : $status;
            });

        if ($normalizedStatuses->isEmpty()) {
            return Status::ITEM_PENDING_INSPECTION;
        }

        if ($normalizedStatuses->every(fn ($status) => $status === Status::ITEM_ACCEPTED)) {
            return Status::ITEM_ACCEPTED;
        }

        if ($normalizedStatuses->contains(Status::ITEM_DEFECTIVE)) {
            return Status::ITEM_DEFECTIVE;
        }

        if ($normalizedStatuses->contains(Status::ITEM_RETURNED)) {
            return Status::ITEM_RETURNED;
        }

        if ($normalizedStatuses->contains(Status::ITEM_REPLACED)) {
            return Status::ITEM_REPLACED;
        }

        return Status::ITEM_PENDING_INSPECTION;
    }

    protected function mapItemStatusesToOrderStatus(array $itemStatuses): int
    {
        $statuses = collect($itemStatuses)
            ->filter(fn ($status) => $status !== null)
            ->map(fn ($status) => (int) $status);

        if ($statuses->isEmpty()) {
            return Status::PO_DELIVERED_PENDING_INSPECTION;
        }

        if ($statuses->contains(Status::ITEM_PENDING_INSPECTION)) {
            return Status::PO_DELIVERED_PENDING_INSPECTION;
        }

        if ($statuses->every(fn ($status) => in_array($status, [Status::ITEM_ACCEPTED, Status::ITEM_RECORDED], true))) {
            return Status::PO_CLOSED;
        }

        return Status::PO_PARTIALLY_DELIVERED;
    }

    protected function accountDisplayName($account): ?string
    {
        if (! $account) {
            return null;
        }

        $employee = $account->employee;
        if ($employee) {
            $fullName = trim((string) $employee->full_name);
            if ($fullName !== '') {
                return $fullName;
            }

            $composed = collect([
                $employee->first_name,
                $employee->middle_name,
                $employee->last_name,
                $employee->suffix,
            ])->filter()->implode(' ');

            if (trim($composed) !== '') {
                return trim($composed);
            }
        }

        return $account->username ?? null;
    }

    protected function resolveReportInspectorName(InspectionReport $report): ?string
    {
        $directInspector = $this->accountDisplayName($report->inspector);
        if ($directInspector) {
            return $directInspector;
        }

        $latestHistory = StatusHistory::query()
            ->with(['account.employee'])
            ->where('table_name', 'inspection_acceptance')
            ->where('record_id', $report->ia_no)
            ->orderByDesc('changed_at')
            ->first();

        return $this->accountDisplayName($latestHistory?->account);
    }
}
