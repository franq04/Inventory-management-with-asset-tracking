<?php

namespace App\Http\Controllers\Custodian;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\InspectionReport;
use App\Models\InspectionReportItem;
use App\Models\FundAllocation;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\Status;
use App\Models\Supplier;
use App\Models\StatusHistory;
use App\Services\FundAllocationService;
use App\Support\PurchaseRequestStatusSynchronizer;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;

class PurchaseOrderController extends Controller
{
    protected function convertiblePurchaseRequestStatuses(): array
    {
        return [
            Status::PR_APPROVED,
        ];
    }

    protected function purchaseRequestUiContext(): array
    {
        $systemManagedStatuses = Status::purchaseRequestLifecycleStatuses();

        $statusOptionIds = array_values(array_unique([
            Status::PR_FOR_APPROVAL,
            Status::PR_APPROVED,
            Status::PR_RECOMMENDED,
            Status::PR_CANCELLED,
            ...$systemManagedStatuses,
        ]));

        $statuses = Status::query()
            ->whereIn('status_id', $statusOptionIds)
            ->orderBy('status_name')
            ->get();

        $user = Auth::user();
        $currentUserName = $user?->employee
            ? collect([
                $user->employee->first_name ?? null,
                $user->employee->middle_name ?? null,
                $user->employee->last_name ?? null,
                $user->employee->suffix ?? null,
            ])->filter()->implode(' ')
            : ($user?->name ?? '');

        $routes = [
            'index' => 'custodian.requests.index',
            'show' => 'custodian.requests.show',
            'update' => 'custodian.requests.update-status',
        ];

        $workflowConfig = [
            'statuses' => $statuses->map(fn ($status) => [
                'id' => $status->status_id,
                'name' => $status->status_name,
            ])->values(),
            'currentUserName' => $currentUserName,
            'showUrlTemplate' => route($routes['show'], ['purchase_request' => '__PR__']),
            'updateUrlTemplate' => route($routes['update'], ['purchase_request' => '__PR__']),
            'approvedStatusId' => Status::PR_APPROVED,
            'declinedStatusId' => Status::PR_CANCELLED,
            'remarksRequiredStatuses' => [Status::PR_CANCELLED],
            'canEditFunds' => true,
            'requiresFundsForApproval' => true,
            'role' => 'custodian',
            'systemManagedStatuses' => $systemManagedStatuses,
        ];

        return [
            'statuses' => $statuses,
            'currentUserName' => $currentUserName,
            'workflowConfig' => $workflowConfig,
            'canEditFunds' => true,
            'workflowRole' => 'custodian',
            'routes' => $routes,
            'routeParam' => 'purchase_request',
            'remarksRequiredStatuses' => [Status::PR_CANCELLED],
            'systemManagedStatuses' => $systemManagedStatuses,
        ];
    }

    public function index(Request $request)
    {
        $searchTerm = $request->string('search')->trim()->toString();
        $registerStage = trim((string) $request->query('stage', ''));
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);

        $approvedRequests = PurchaseRequest::query()
            ->select([
                'pr_no',
                'account_id',
                'division_id',
                'section_id',
                'purpose',
                'created_at',
            ])
            ->with([
                'requester:account_id,username',
                'requester.employee:employee_id,account_id,first_name,middle_name,last_name,suffix',
                'division:division_id,division_name',
                'section:section_id,section_name',
            ])
            ->whereIn('status_id', $this->convertiblePurchaseRequestStatuses())
            ->whereDoesntHave('purchaseOrder')
            ->orderByDesc('created_at')
            ->paginate(3, ['*'], 'approved_page')
            ->withQueryString();

        $purchaseOrderQuery = $this->purchaseOrderIndexQuery($searchTerm, $registerStage, $dateFrom, $dateTo);

        $ordersForPipeline = (clone $purchaseOrderQuery)->get();
        $ordersForPipeline->each(function (PurchaseOrder $order) {
            $order->setRelation('inspectionReports', $order->inspectionReports->sortBy('inspection_date')->values());
        });

        $pipelineStages = $this->buildPipeline($ordersForPipeline);
        $purchaseOrders = $purchaseOrderQuery->paginate(5)->withQueryString();
        $registerSummaries = $purchaseOrders->getCollection()
            ->mapWithKeys(fn (PurchaseOrder $order) => [$order->po_no => $this->summarizeOrderForRegister($order)])
            ->all();

        $viewData = [
            'approvedRequests' => $approvedRequests,
            'pipelineStages' => $pipelineStages,
            'purchaseOrders' => $purchaseOrders,
            'registerSummaries' => $registerSummaries,
            'search' => $searchTerm,
            'registerStage' => $registerStage,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ];

        return view('custodian.purchase_orders.pipeline', array_merge($viewData, $this->purchaseRequestUiContext()));
    }

    protected function purchaseOrderIndexQuery(?string $searchTerm = null, ?string $stage = null, ?string $dateFrom = null, ?string $dateTo = null): Builder
    {
        $query = PurchaseOrder::query()
            ->select([
                'po_no',
                'pr_no',
                'supplier_id',
                'status_id',
                'order_date',
                'delivery_date',
                'created_at',
            ])
            ->with([
                'supplier:supplier_id,supplier_name',
                'status:status_id,status_name',
                'items' => function ($itemQuery) {
                    $itemQuery->select([
                        'poi_id',
                        'po_no',
                        'item_description',
                        'quantity',
                        'unit',
                        'unit_cost',
                        'fulfillment_status',
                        'alternate_description',
                        'employee_decision',
                        'employee_wait_until',
                        'received_at',
                    ]);
                },
                'items.latestInspectionItem' => function ($inspectionItemQuery) {
                    $inspectionItemQuery->select([
                        'inspection_report_items.ia_item_id',
                        'inspection_report_items.po_item_id',
                        'inspection_report_items.inspection_status_id',
                        'inspection_report_items.ia_no',
                    ]);
                },
                'inspectionReports:ia_no,po_no,inspection_date,overall_status_id',
            ])
            ->orderByDesc('order_date')
            ->orderByDesc('created_at');

        $searchTerm = trim((string) $searchTerm);

        if ($searchTerm !== '') {
            $query->where(function ($subQuery) use ($searchTerm) {
                $value = '%'.$searchTerm.'%';
                $subQuery->where('po_no', 'like', $value)
                    ->orWhere('pr_no', 'like', $value)
                    ->orWhereHas('supplier', function ($supplierQuery) use ($value) {
                        $supplierQuery->where('supplier_name', 'like', $value);
                    });
            });
        }

        $stage = trim((string) $stage);
        if ($stage !== '') {
            $query->where(function ($stageQuery) use ($stage) {
                if ($stage === 'awaiting_delivery') {
                    $stageQuery->whereIn('status_id', [Status::PO_CREATED, Status::PO_SENT_TO_SUPPLIER]);
                } elseif ($stage === 'receiving') {
                    $stageQuery->where('status_id', Status::PO_PARTIALLY_DELIVERED);
                } elseif ($stage === 'inspection') {
                    $stageQuery->where('status_id', Status::PO_DELIVERED_PENDING_INSPECTION);
                } elseif ($stage === 'issues') {
                    $stageQuery->where('status_id', Status::PO_CANCELLED);
                } elseif ($stage === 'completed') {
                    $stageQuery->where('status_id', Status::PO_CLOSED);
                }
            });
        }

        if ($dateFrom && $dateTo) {
            $query->whereBetween('order_date', [$dateFrom, $dateTo]);
        }

        return $query;
    }

    public function printPdf(Request $request): View
    {
        $searchTerm = trim((string) $request->query('search', ''));
        $registerStage = trim((string) $request->query('stage', ''));
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);

        $purchaseOrders = $this->purchaseOrderIndexQuery($searchTerm, $registerStage, $dateFrom, $dateTo)
            ->get();

        $account = Auth::user();
        $preparedByName = $account?->employee?->full_name
            ?: $account?->username
            ?: 'System User';
        $preparedByRole = $account?->role
            ? ucfirst(str_replace('_', ' ', (string) $account->role))
            : 'User';

        return view('custodian.purchase_orders.print', [
            'purchaseOrders' => $purchaseOrders,
            'generatedOnLabel' => $this->buildGeneratedOnLabel($dateFrom, $dateTo),
            'preparedByName' => $preparedByName,
            'preparedByRole' => $preparedByRole,
        ]);
    }

    public function exportExcel(Request $request): Response
    {
        $searchTerm = trim((string) $request->query('search', ''));
        $registerStage = trim((string) $request->query('stage', ''));
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);

        $purchaseOrders = $this->purchaseOrderIndexQuery($searchTerm, $registerStage, $dateFrom, $dateTo)
            ->get();

        $account = Auth::user();
        $preparedByName = $account?->employee?->full_name
            ?: $account?->username
            ?: 'System User';
        $preparedByRole = $account?->role
            ? ucfirst(str_replace('_', ' ', (string) $account->role))
            : 'User';

        $html = view('custodian.purchase_orders.excel', [
            'purchaseOrders' => $purchaseOrders,
            'generatedOnLabel' => $this->buildGeneratedOnLabel($dateFrom, $dateTo),
            'preparedByName' => $preparedByName,
            'preparedByRole' => $preparedByRole,
        ])->render();

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="Purchase-Order-Register-'.date('Y-m-d').'.xls"');
    }

    protected function resolveDateRange(Request $request): array
    {
        $dateFromRaw = trim((string) $request->query('date_from', ''));
        $dateToRaw = trim((string) $request->query('date_to', ''));

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

    protected function buildGeneratedOnLabel(?string $dateFrom, ?string $dateTo): string
    {
        if ($dateFrom && $dateTo) {
            return Carbon::parse($dateFrom)->format('F d, Y').' - '.Carbon::parse($dateTo)->format('F d, Y');
        }

        return now()->format('F d, Y');
    }

    public function create(Request $request)
    {
        $requests = PurchaseRequest::with(['items', 'status', 'fundAllocation'])
            ->whereIn('status_id', $this->convertiblePurchaseRequestStatuses())
            ->whereDoesntHave('purchaseOrder')
            ->orderByDesc('created_at')
            ->get();

        $suppliers = Supplier::orderBy('supplier_name')->get();
        $custodians = Account::where('role', 'custodian')->orderBy('username')->get();
        $initialPr = $request->query('pr');

        return view('custodian.purchase_orders.create', [
            'requests' => $requests,
            'suppliers' => $suppliers,
            'custodians' => $custodians,
            'initialPr' => $initialPr,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pr_no' => 'required|exists:purchase_requests,pr_no',
            'supplier_mode' => ['required', Rule::in(['existing', 'new'])],
            'supplier_id' => ['required_if:supplier_mode,existing', 'nullable', 'exists:suppliers,supplier_id'],
            'new_supplier' => ['sometimes', 'array'],
            'new_supplier.name' => ['required_if:supplier_mode,new', 'nullable', 'string', 'max:150'],
            'new_supplier.address' => ['nullable', 'string', 'max:255'],
            'new_supplier.tin' => ['nullable', 'string', 'max:50'],
            'new_supplier.contact_person' => ['nullable', 'string', 'max:150'],
            'new_supplier.contact_no' => ['nullable', 'string', 'max:50'],
            'new_supplier.email' => ['nullable', 'email', 'max:150'],
            'order_date' => 'required|date',
            'delivery_date' => 'required|date',
            'mode_of_procurement' => 'nullable|string|max:100',
            'place_of_delivery' => 'required|string|max:255',
            'delivery_term' => 'required|string|max:100',
            'payment_term' => 'required|string|max:100',
            'fund_cluster' => 'nullable|string|max:50',
            'funds_available' => 'nullable|numeric|min:0',
            'ors_burs_no' => 'nullable|string|max:100',
            'ors_burs_date' => 'nullable|date',
            'ors_burs_amount' => 'nullable|numeric|min:0',
            'amount_in_words' => 'nullable|string|max:255',
            'conforme_name' => 'nullable|string|max:150',
            'conforme_date' => 'nullable|date',
            'authorized_by' => 'nullable|exists:accounts,account_id',
            'ordered_by' => 'nullable|exists:accounts,account_id',
            'remarks' => 'nullable|string',
        ]);

        $account = Auth::user();

        $supplierMode = $validated['supplier_mode'];
        $supplierId = $validated['supplier_id'] ?? null;
        if ($supplierMode === 'new') {
            $newSupplier = $validated['new_supplier'] ?? [];
            $supplierId = $this->generateSupplierId();
            $supplierName = trim((string) ($newSupplier['name'] ?? ''));

            if ($supplierName === '') {
                throw ValidationException::withMessages([
                    'new_supplier.name' => 'Please provide the supplier name.',
                ]);
            }

            $supplierAttributes = [
                'supplier_id' => $supplierId,
                'supplier_name' => $supplierName,
                'address' => $newSupplier['address'] ?? null,
                'contact_person' => $newSupplier['contact_person'] ?? null,
                'contact_no' => $newSupplier['contact_no'] ?? null,
                'email' => $newSupplier['email'] ?? null,
            ];

            if (Schema::hasColumn('suppliers', 'tin')) {
                $supplierAttributes['tin'] = $newSupplier['tin'] ?? null;
            }

            Supplier::create($supplierAttributes);

            $validated['supplier_id'] = $supplierId;
        }

        unset($validated['new_supplier'], $validated['supplier_mode']);


        [$purchaseOrder, $orderItems] = DB::transaction(function () use ($validated, $account) {
            $purchaseRequest = PurchaseRequest::with('items')
                ->where('pr_no', $validated['pr_no'])
                ->lockForUpdate()
                ->firstOrFail();

            $fundAllocation = $purchaseRequest->fund_allocation_id
                ? FundAllocation::query()
                    ->whereKey($purchaseRequest->fund_allocation_id)
                    ->lockForUpdate()
                    ->first()
                : null;

            if (! $fundAllocation) {
                throw ValidationException::withMessages([
                    'pr_no' => 'The selected purchase request has no valid fund allocation.',
                ]);
            }

            if ((int) $purchaseRequest->status_id !== Status::PR_APPROVED) {
                throw ValidationException::withMessages([
                    'pr_no' => 'The selected purchase request must be approved before generating a purchase order.',
                ]);
            }

            if ($purchaseRequest->items->isEmpty()) {
                throw ValidationException::withMessages([
                    'pr_no' => 'The approved purchase request does not have any items to convert.',
                ]);
            }

            // Self-heal stale remaining values (legacy PR-time reservations)
            // before validating/deducting for PO generation.
            $liveFundsBeforeDeduction = $fundAllocation->syncRemainingAmount();

            $requiredAmount = (float) $purchaseRequest->total_estimated_cost;
            if ($liveFundsBeforeDeduction < $requiredAmount) {
                throw ValidationException::withMessages([
                    'pr_no' => 'Funds available must cover the total estimated cost before generating a purchase order.',
                ]);
            }

            $fundService = new FundAllocationService();
            if (! $fundService->reserve($fundAllocation, $requiredAmount, $purchaseRequest->pr_no)) {
                throw ValidationException::withMessages([
                    'pr_no' => 'Unable to reserve funds for this purchase order. Please try again.',
                ]);
            }

            $fundAllocation->refresh();
            $postDeductionRemaining = (float) $fundAllocation->remaining_amount;

            $hasAttentionItems = $purchaseRequest->items->contains(function (PurchaseRequestItem $item) {
                return in_array($item->fulfillment_status, ['alternative', 'unavailable'], true);
            });

            $poStatusId = $hasAttentionItems ? Status::PO_CREATED : Status::PO_SENT_TO_SUPPLIER;

            $poNumber = $this->generatePoNumber();

            $purchaseOrder = PurchaseOrder::create([
                'po_no' => $poNumber,
                'pr_no' => $purchaseRequest->pr_no,
                'supplier_id' => $validated['supplier_id'],
                'order_date' => $validated['order_date'],
                'delivery_date' => $validated['delivery_date'] ?? null,
                'mode_of_procurement' => $validated['mode_of_procurement'] ?? null,
                'place_of_delivery' => $validated['place_of_delivery'] ?? null,
                'delivery_term' => $validated['delivery_term'] ?? null,
                'payment_term' => $validated['payment_term'] ?? null,
                'fund_cluster' => $validated['fund_cluster'] ?? $purchaseRequest->fund_cluster ?? $fundAllocation->fund_cluster,
                'funds_available' => $postDeductionRemaining,
                'ors_burs_no' => $validated['ors_burs_no'] ?? null,
                'ors_burs_date' => $validated['ors_burs_date'] ?? null,
                'ors_burs_amount' => $validated['ors_burs_amount'] ?? null,
                'amount_in_words' => $validated['amount_in_words'] ?? null,
                'conforme_name' => $validated['conforme_name'] ?? null,
                'conforme_date' => $validated['conforme_date'] ?? null,
                'authorized_by' => $validated['authorized_by'] ?? null,
                'ordered_by' => $validated['ordered_by'] ?? $account->account_id,
                'status_id' => $poStatusId,
                'remarks' => $validated['remarks'] ?? null,
            ]);

            $orderItems = $purchaseRequest->items->map(function (PurchaseRequestItem $item) use ($purchaseOrder) {
                $status = $item->fulfillment_status === 'pending' ? 'ordered' : $item->fulfillment_status;

                return PurchaseOrderItem::create([
                    'po_no' => $purchaseOrder->po_no,
                    'pri_id' => $item->pri_id,
                    'item_description' => $item->item_description,
                    'quantity' => $item->quantity,
                    'unit' => $item->unit,
                    'unit_cost' => $status === 'unavailable' ? 0 : (float) $item->estimated_unit_cost,
                    'remarks' => $item->remarks,
                    'fulfillment_status' => $status ?? 'ordered',
                    'alternate_description' => $status === 'alternative' ? $item->alternate_description : null,
                    'employee_decision' => $item->employee_decision,
                    'employee_decided_at' => $item->employee_decided_at,
                    'employee_wait_until' => $item->employee_wait_until,
                    'employee_wait_note' => $item->employee_wait_note,
                ]);
            });

            $now = now();

            $purchaseRequest->fund_cluster = $purchaseRequest->fund_cluster ?: $fundAllocation->fund_cluster;
            $purchaseRequest->funds_available = $postDeductionRemaining;

            $purchaseRequest->approved_by = $purchaseRequest->approved_by ?: $account->account_id;
            $purchaseRequest->approved_at = $purchaseRequest->approved_at ?: $now;
            if (! $purchaseRequest->approval_remarks) {
                $purchaseRequest->approval_remarks = 'Confirmed during purchase order generation.';
            }

            $purchaseRequest->reviewed_by = $account->account_id;
            $purchaseRequest->save();

            StatusHistory::create([
                'table_name' => 'purchase_requests',
                'record_id' => $purchaseRequest->pr_no,
                'old_status_id' => Status::PR_APPROVED,
                'new_status_id' => Status::PR_APPROVED,
                'changed_by' => $account->account_id,
                'remarks' => 'Converted to purchase order '.$purchaseOrder->po_no,
                'changed_at' => $now,
            ]);

            AuditLog::create([
                'account_id' => $account->account_id,
                'table_name' => 'purchase_orders',
                'action' => 'CREATE',
                'description' => sprintf('Generated purchase order %s from request %s', $purchaseOrder->po_no, $purchaseRequest->pr_no),
            ]);

            PurchaseRequestStatusSynchronizer::sync(
                $purchaseOrder,
                $account->account_id,
                sprintf('Purchase order %s generated from this request.', $purchaseOrder->po_no)
            );

            $purchaseOrder->setRelation('purchaseRequest', $purchaseRequest);

            return [$purchaseOrder, $orderItems];
        });

        $this->notifyStakeholdersOfPurchaseOrder($purchaseOrder);
        $this->notifyRequesterOfItemFulfillment($purchaseOrder, $orderItems);

        return response()->json([
            'status' => 'success',
            'message' => 'Purchase order created successfully.',
            'redirect' => route('custodian.orders.index'),
        ]);
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['items', 'supplier', 'purchaseRequest.fundAllocation', 'status']);

        // Authorized official in the details view should reflect the custodian who created the PO.
        $officialAccountId = $purchaseOrder->ordered_by ?: $purchaseOrder->authorized_by;
        $officialAccount = $officialAccountId
            ? Account::query()->with('employee.position')->find($officialAccountId)
            : null;

        $authorizedOfficialName = null;
        if ($officialAccount?->employee) {
            $authorizedOfficialName = $officialAccount->employee->full_name;
        }
        if (empty($authorizedOfficialName)) {
            $authorizedOfficialName = $officialAccount?->username;
        }

        $authorizedOfficialDesignation = $officialAccount?->employee?->position?->position_title;
        $authorizedOfficialSignature = $this->signatureDataUri($officialAccount?->employee?->signature);

        $effectiveFundCluster = $purchaseOrder->fund_cluster
            ?: $purchaseOrder->purchaseRequest?->fund_cluster
            ?: $purchaseOrder->purchaseRequest?->fundAllocation?->fund_cluster;

        $effectiveFundsAvailable = $purchaseOrder->funds_available;
        if ($effectiveFundsAvailable === null) {
            $effectiveFundsAvailable = $purchaseOrder->purchaseRequest?->fundAllocation?->syncRemainingAmount()
                ?? $purchaseOrder->purchaseRequest?->funds_available;
        }

        // Get status history for this PO
        $statusHistory = StatusHistory::query()
            ->where('table_name', 'purchase_orders')
            ->where('record_id', $purchaseOrder->po_no)
            ->with(['account.employee', 'status'])
            ->orderByDesc('changed_at')
            ->get()
            ->map(function (StatusHistory $history) {
                $changedBy = null;
                if ($history->account) {
                    $employee = $history->account->employee;
                    if ($employee) {
                        $changedBy = collect([
                            $employee->first_name ?? null,
                            $employee->middle_name ?? null,
                            $employee->last_name ?? null,
                            $employee->suffix ?? null,
                        ])->filter()->implode(' ');
                    }
                    if (empty($changedBy)) {
                        $changedBy = $history->account->username ?? 'Unknown';
                    }
                }

                $oldStatus = $history->old_status_id
                    ? Status::find($history->old_status_id)?->status_name
                    : null;

                return [
                    'changed_at' => optional($history->changed_at)->format('M d, Y h:i A'),
                    'changed_by' => $changedBy,
                    'old_status' => $oldStatus,
                    'new_status' => $history->status?->status_name,
                    'remarks' => $history->remarks,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => [
                'po_no' => $purchaseOrder->po_no,
                'pr_no' => $purchaseOrder->pr_no,
                'supplier' => $purchaseOrder->supplier?->supplier_name,
                'supplier_address' => $purchaseOrder->supplier?->address,
                'supplier_tin' => $purchaseOrder->supplier?->tin,
                'mode_of_procurement' => $purchaseOrder->mode_of_procurement,
                'place_of_delivery' => $purchaseOrder->place_of_delivery,
                'delivery_term' => $purchaseOrder->delivery_term,
                'payment_term' => $purchaseOrder->payment_term,
                'status' => $purchaseOrder->status?->status_name,
                'order_date' => optional($purchaseOrder->order_date)->toDateString(),
                'delivery_date' => optional($purchaseOrder->delivery_date)->toDateString(),
                'amount_in_words' => $purchaseOrder->amount_in_words,
                'fund_cluster' => $effectiveFundCluster,
                'funds_available' => $effectiveFundsAvailable,
                'ors_burs_no' => $purchaseOrder->ors_burs_no,
                'ors_burs_date' => optional($purchaseOrder->ors_burs_date)->toDateString(),
                'ors_burs_amount' => $purchaseOrder->ors_burs_amount,
                'conforme_name' => $purchaseOrder->conforme_name,
                'conforme_date' => optional($purchaseOrder->conforme_date)->toDateString(),
                'authorized_official_name' => $authorizedOfficialName,
                'authorized_official_designation' => $authorizedOfficialDesignation,
                'authorized_official_signature' => $authorizedOfficialSignature,
                'items' => $purchaseOrder->items->map(function (PurchaseOrderItem $item) {
                    return [
                        'item_description' => $item->item_description,
                        'quantity' => $item->quantity,
                        'unit' => $item->unit,
                        'unit_cost' => $item->unit_cost,
                        'total_cost' => $item->total_cost,
                        'remarks' => $item->remarks,
                        'fulfillment_status' => $item->fulfillment_status,
                        'alternate_description' => $item->alternate_description,
                        'employee_decision' => $item->employee_decision,
                        'employee_decided_at' => optional($item->employee_decided_at)->toDateTimeString(),
                        'employee_wait_until' => optional($item->employee_wait_until)->toDateString(),
                        'employee_wait_note' => $item->employee_wait_note,
                    ];
                }),
                'status_history' => $statusHistory,
            ],
        ]);
    }

    private function signatureDataUri($signature): ?string
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

        return 'data:image/png;base64,' . base64_encode($signature);
    }

    public function acceptOrder(Request $request, PurchaseOrder $purchaseOrder)
    {
        if ((int) $purchaseOrder->status_id !== Status::PO_CREATED) {
            return response()->json([
                'status' => 'error',
                'message' => 'This purchase order cannot be accepted at this time.',
            ], 422);
        }

        $account = Auth::user();

        DB::transaction(function () use ($purchaseOrder, $account) {
            $oldStatus = $purchaseOrder->status_id;
            $purchaseOrder->status_id = Status::PO_SENT_TO_SUPPLIER;
            $purchaseOrder->save();

            StatusHistory::create([
                'table_name' => 'purchase_orders',
                'record_id' => $purchaseOrder->po_no,
                'old_status_id' => $oldStatus,
                'new_status_id' => Status::PO_SENT_TO_SUPPLIER,
                'changed_by' => $account->account_id,
                'remarks' => 'Purchase order accepted and sent to supplier.',
                'changed_at' => now(),
            ]);

            AuditLog::create([
                'account_id' => $account->account_id,
                'table_name' => 'purchase_orders',
                'action' => 'UPDATE',
                'description' => sprintf('Accepted purchase order %s and marked as sent to supplier', $purchaseOrder->po_no),
            ]);

            PurchaseRequestStatusSynchronizer::sync(
                $purchaseOrder,
                $account->account_id,
                sprintf('Purchase order %s accepted and sent to the supplier.', $purchaseOrder->po_no)
            );
        });

        $this->notifyStakeholdersOfOrderAcceptance($purchaseOrder);

        return response()->json([
            'status' => 'success',
            'message' => 'Purchase order has been accepted and sent to the supplier.',
        ]);
    }

    public function receiveItem(Request $request, PurchaseOrderItem $purchaseOrderItem)
    {
        $validated = $request->validate([
            'receiving_note' => ['nullable', 'string', 'max:500'],
        ]);

        if ($purchaseOrderItem->received_at) {
            return response()->json([
                'status' => 'error',
                'message' => 'This item has already been marked as received.',
            ], 422);
        }

        $account = Auth::user();

        if (! $account) {
            abort(403, 'Only authenticated custodians may receive items.');
        }

        [$updatedItem, $purchaseOrder, $allReceived, $statusChanged] = DB::transaction(function () use ($purchaseOrderItem, $account, $validated) {
            $purchaseOrderItem->received_at = now();
            $purchaseOrderItem->received_by = $account->account_id;
            $purchaseOrderItem->receiving_note = $validated['receiving_note'] ?? null;
            $purchaseOrderItem->save();

            $purchaseOrder = $purchaseOrderItem->purchaseOrder()->lockForUpdate()->firstOrFail();
            $purchaseOrder->load('items');

            $totalItems = $purchaseOrder->items->count();
            $receivedCount = $purchaseOrder->items->whereNotNull('received_at')->count();
            $allReceived = $totalItems > 0 && $receivedCount === $totalItems;

            $targetStatus = $allReceived ? Status::PO_DELIVERED_PENDING_INSPECTION : Status::PO_PARTIALLY_DELIVERED;
            $statusChanged = false;

            if ((int) $purchaseOrder->status_id !== $targetStatus) {
                $oldStatus = $purchaseOrder->status_id;
                $purchaseOrder->status_id = $targetStatus;
                $purchaseOrder->save();

                StatusHistory::create([
                    'table_name' => 'purchase_orders',
                    'record_id' => $purchaseOrder->po_no,
                    'old_status_id' => $oldStatus,
                    'new_status_id' => $targetStatus,
                    'changed_by' => $account->account_id,
                    'remarks' => 'Updated after receiving purchase order items.',
                    'changed_at' => now(),
                ]);

                $statusChanged = true;

                PurchaseRequestStatusSynchronizer::sync(
                    $purchaseOrder,
                    $account->account_id,
                    $allReceived
                        ? sprintf('All items received for purchase order %s.', $purchaseOrder->po_no)
                        : sprintf('Receiving progress recorded for purchase order %s.', $purchaseOrder->po_no)
                );
            }

            if ($allReceived) {
                $this->ensurePendingInspectionReport($purchaseOrder);
            }

            AuditLog::create([
                'account_id' => $account->account_id,
                'table_name' => 'purchase_order_items',
                'action' => 'UPDATE',
                'description' => sprintf('Marked purchase order item %d on %s as received', $purchaseOrderItem->poi_id, $purchaseOrderItem->po_no),
            ]);

            return [
                $purchaseOrderItem->fresh(['receivedBy']),
                $purchaseOrder->fresh(['items.receivedBy', 'items.latestInspectionItem', 'inspectionReports']),
                $allReceived,
                $statusChanged,
            ];
        });

        $this->notifyStakeholdersOfReceivingProgress($purchaseOrder, $updatedItem, $allReceived);

        $message = $allReceived
            ? 'All items for this purchase order are now received and ready for inspection.'
            : 'Item marked as received. Remaining items stay on the receiving queue.';

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'order_status' => $purchaseOrder->status_id,
            'status_changed' => $statusChanged,
        ]);
    }

    public function updateItem(Request $request, PurchaseOrderItem $purchaseOrderItem)
    {
        $validated = $request->validate([
            'fulfillment_status' => ['required', Rule::in(['ordered', 'unavailable', 'alternative'])],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'alternate_description' => ['nullable', 'string', 'max:255'],
        ]);

        $alternateDescription = trim((string) ($validated['alternate_description'] ?? ''));
        if ($validated['fulfillment_status'] === 'alternative' && $alternateDescription === '') {
            throw ValidationException::withMessages([
                'alternate_description' => 'Please describe the alternative item you are proposing.',
            ]);
        }

        $previousStatus = $purchaseOrderItem->fulfillment_status;
        $previousCost = (float) $purchaseOrderItem->unit_cost;
        $previousAlternate = $purchaseOrderItem->alternate_description;
        $previousWaitUntil = $purchaseOrderItem->employee_wait_until;

        $updatedItem = DB::transaction(function () use ($purchaseOrderItem, $validated, $alternateDescription, $previousStatus) {
            $status = $validated['fulfillment_status'];
            $unitCost = array_key_exists('unit_cost', $validated)
                ? (float) ($validated['unit_cost'] ?? 0)
                : (float) $purchaseOrderItem->unit_cost;

            if ($status === 'unavailable') {
                $unitCost = 0;
            }

            $purchaseOrderItem->fulfillment_status = $status;
            $purchaseOrderItem->unit_cost = $unitCost;
            $purchaseOrderItem->alternate_description = $status === 'alternative' ? $alternateDescription : null;
            $purchaseOrderItem->employee_decision = null;
            $purchaseOrderItem->employee_decided_at = null;
            if ($status !== 'unavailable') {
                $purchaseOrderItem->employee_wait_until = null;
                $purchaseOrderItem->employee_wait_note = null;
            } elseif ($previousStatus !== 'unavailable') {
                $purchaseOrderItem->employee_wait_until = null;
                $purchaseOrderItem->employee_wait_note = null;
            }
            $purchaseOrderItem->save();

            $purchaseOrderItem->loadMissing('purchaseOrder', 'purchaseOrder.items', 'purchaseOrder.purchaseRequest');

            $this->synchronizeOrderAndRequestStatus($purchaseOrderItem->purchaseOrder);

            AuditLog::create([
                'account_id' => Auth::id() ?: $purchaseOrderItem->purchaseOrder?->ordered_by,
                'table_name' => 'purchase_order_items',
                'action' => 'UPDATE',
                'description' => sprintf('Updated fulfillment details for item %d on PO %s', $purchaseOrderItem->poi_id, $purchaseOrderItem->po_no),
            ]);

            return $purchaseOrderItem->fresh();
        });

        $costChanged = abs($previousCost - (float) $updatedItem->unit_cost) > 0.009;
        $statusChanged = $previousStatus !== $updatedItem->fulfillment_status;
        $alternateChanged = $previousAlternate !== $updatedItem->alternate_description;
        $waitChanged = optional($previousWaitUntil)->toDateString() !== optional($updatedItem->employee_wait_until)->toDateString();

        if ($statusChanged || $costChanged || $alternateChanged || $waitChanged) {
            $this->notifyRequesterOfItemUpdate($updatedItem, $statusChanged, $costChanged, $waitChanged);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Purchase order item updated successfully.',
        ]);
    }

    public function retainOriginal(Request $request, PurchaseOrderItem $purchaseOrderItem)
    {
        $purchaseOrderItem->loadMissing('purchaseOrder', 'purchaseOrder.purchaseRequest');

        if ($purchaseOrderItem->fulfillment_status !== 'alternative' || $purchaseOrderItem->employee_decision !== 'wait') {
            return response()->json([
                'status' => 'error',
                'message' => 'Only alternative items with a pending wait decision can be retained as the original request.',
            ], 422);
        }

        $updatedItem = DB::transaction(function () use ($purchaseOrderItem) {
            $purchaseOrderItem->fulfillment_status = 'unavailable';
            $purchaseOrderItem->alternate_description = null;
            $purchaseOrderItem->unit_cost = 0;
            $purchaseOrderItem->save();

            $this->synchronizeOrderAndRequestStatus($purchaseOrderItem->purchaseOrder);

            AuditLog::create([
                'account_id' => Auth::id() ?: $purchaseOrderItem->purchaseOrder?->ordered_by,
                'table_name' => 'purchase_order_items',
                'action' => 'UPDATE',
                'description' => sprintf('Retained original item for PO %s (item %d)', $purchaseOrderItem->po_no, $purchaseOrderItem->poi_id),
            ]);

            return $purchaseOrderItem->fresh();
        });

        $this->notifyRequesterOfItemUpdate($updatedItem, true, false);

        return response()->json([
            'status' => 'success',
            'message' => 'The original item will remain on backorder based on the employee timeframe.',
        ]);
    }

    protected function generatePoNumber(): string
    {
        $datePart = now()->format('Ymd');
        $prefix = 'PO-' . $datePart . '-';

        $last = PurchaseOrder::where('po_no', 'like', $prefix.'%')
            ->orderByDesc('po_no')
            ->first();

        $sequence = 1;

        if ($last) {
            $sequence = (int) Str::afterLast($last->po_no, '-') + 1;
        }

        return $prefix . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
    }

    protected function generateSupplierId(): string
    {
        do {
            $identifier = 'SUP-' . Str::upper(Str::random(8));
        } while (Supplier::whereKey($identifier)->exists());

        return $identifier;
    }

    protected function pipelineStageMeta(): array
    {
        return [
            'awaiting_delivery' => [
                'label' => 'Awaiting Delivery',
                'description' => 'Orders sent to suppliers or still being lined up for delivery.',
                'icon' => 'fa-truck-ramp-box',
                'badge' => 'bg-sky-100 text-sky-800',
            ],
            'receiving' => [
                'label' => 'Receiving Queue',
                'description' => 'Deliveries in progress or partially received that need follow-up.',
                'icon' => 'fa-boxes-packing',
                'badge' => 'bg-amber-100 text-amber-700',
            ],
            'inspection' => [
                'label' => 'Inspection & Acceptance',
                'description' => 'Batches awaiting inspection results or documentation.',
                'icon' => 'fa-clipboard-check',
                'badge' => 'bg-emerald-100 text-emerald-700',
            ],
            'issues' => [
                'label' => 'With Issues',
                'description' => 'Deliveries that produced rejections, returns, or cancellations.',
                'icon' => 'fa-triangle-exclamation',
                'badge' => 'bg-rose-100 text-rose-700',
            ],
            'completed' => [
                'label' => 'Closed / Recorded',
                'description' => 'Orders fully accepted and recorded in property ledgers.',
                'icon' => 'fa-circle-check',
                'badge' => 'bg-slate-100 text-slate-700',
            ],
        ];
    }

    protected function buildPipeline(Collection $orders): array
    {
        $stages = collect($this->pipelineStageMeta())->map(function (array $meta, string $key) {
            return array_merge($meta, [
                'key' => $key,
                'orders' => collect(),
            ]);
        });

        foreach ($orders as $order) {
            $summary = $this->summarizeOrderForPipeline($order);
            $stageKey = $this->resolvePipelineStage($order);

            if (! $stages->has($stageKey)) {
                $stageKey = 'awaiting_delivery';
            }

            $stages[$stageKey]['orders']->push($summary);

            if ($summary['has_issues'] && $stageKey !== 'issues') {
                $stages['issues']['orders']->push($summary);
            }
        }

        return $stages->map(function (array $stage) {
            $stage['count'] = $stage['orders']->count();
            $stage['orders'] = $stage['orders']->sortBy(function (array $summary) {
                $order = $summary['order'];
                $orderDate = $order->order_date ? $order->order_date->timestamp : null;
                return $orderDate ?? optional($order->created_at)->timestamp ?? 0;
            })->reverse()->values();

            return $stage;
        })->values()->all();
    }

    protected function resolvePipelineStage(PurchaseOrder $order): string
    {
        return match ((int) $order->status_id) {
            Status::PO_PARTIALLY_DELIVERED => 'receiving',
            Status::PO_DELIVERED_PENDING_INSPECTION => 'inspection',
            Status::PO_CLOSED => 'completed',
            Status::PO_CANCELLED => 'issues',
            default => 'awaiting_delivery',
        };
    }

    protected function collectLatestInspectionItems(PurchaseOrder $order): Collection
    {
        return $order->items
            ->mapWithKeys(function (PurchaseOrderItem $item) {
                $latestItem = $item->latestInspectionItem;

                if (! $latestItem) {
                    return [];
                }

                return [$item->poi_id => $latestItem];
            });
    }

    protected function summarizeOrderForPipeline(PurchaseOrder $order): array
    {
        $latestInspectionItems = $this->collectLatestInspectionItems($order);

        $issueStatusIds = [
            Status::ITEM_DEFECTIVE,
            Status::ITEM_RETURNED,
            Status::ITEM_REPLACED,
        ];

        $acceptedStatusIds = [
            Status::ITEM_ACCEPTED,
            Status::ITEM_RECORDED,
        ];

        $issueItems = $latestInspectionItems->filter(function (InspectionReportItem $item) use ($issueStatusIds) {
            return in_array((int) $item->inspection_status_id, $issueStatusIds, true);
        });

        $acceptedItems = $latestInspectionItems->filter(function (InspectionReportItem $item) use ($acceptedStatusIds) {
            return in_array((int) $item->inspection_status_id, $acceptedStatusIds, true);
        });

    $totalItems = $order->items->count();
    $receivedCount = $order->items->whereNotNull('received_at')->count();
    $awaitingReceipt = max($totalItems - $receivedCount, 0);
        $resolvedCount = $acceptedItems->count() + $issueItems->count();
        $pendingItems = max($totalItems - $resolvedCount, 0);

        $issueBreakdown = $issueItems->groupBy('inspection_status_id')->map(function (Collection $group, $statusId) {
            return [
                'status_id' => (int) $statusId,
                'label' => 'Issue',
                'count' => $group->count(),
            ];
        })->values();

        $sortedReports = $order->inspectionReports->sortByDesc('inspection_date');
        $latestReport = $sortedReports->first();
        $earliestReport = $order->inspectionReports->sortBy('inspection_date')->first();

        $expectedDelivery = $order->delivery_date;
        $firstDeliveryDate = $earliestReport?->inspection_date;

        $timing = [
            'expected' => $expectedDelivery,
            'first_delivery' => $firstDeliveryDate,
            'arrived_early' => false,
            'early_days' => 0,
            'overdue' => false,
            'overdue_days' => 0,
        ];

        if ($expectedDelivery) {
            if ($firstDeliveryDate) {
                if ($firstDeliveryDate->lt($expectedDelivery)) {
                    $daysAhead = $expectedDelivery->diffInDays($firstDeliveryDate);
                    if ($daysAhead > 0) {
                        $timing['arrived_early'] = true;
                        $timing['early_days'] = $daysAhead;
                    }
                } elseif ($firstDeliveryDate->gt($expectedDelivery)) {
                    $daysLate = $firstDeliveryDate->diffInDays($expectedDelivery);
                    if ($daysLate > 0) {
                        $timing['overdue'] = true;
                        $timing['overdue_days'] = $daysLate;
                    }
                }
            } elseif (now()->greaterThan($expectedDelivery)) {
                $timing['overdue'] = true;
                $timing['overdue_days'] = max($expectedDelivery->diffInDays(now()), 1);
            }
        }

        return [
            'order' => $order,
            'totals' => [
                'items' => $totalItems,
                'received' => $receivedCount,
                'awaiting_receipt' => $awaitingReceipt,
                'accepted' => $acceptedItems->count(),
                'issues' => $issueItems->count(),
                'pending' => $pendingItems,
            ],
            'issue_breakdown' => $issueBreakdown,
            'deliveries' => [
                'count' => $sortedReports->count(),
                'latest_inspection_date' => optional($latestReport?->inspection_date)->toDateString(),
                'latest_report' => $latestReport,
            ],
            'latest_items' => $latestInspectionItems,
            'has_issues' => $issueItems->isNotEmpty(),
            'timing' => $timing,
        ];
    }

    protected function summarizeOrderForRegister(PurchaseOrder $order): array
    {
        $latestInspectionItems = $this->collectLatestInspectionItems($order);

        $issueStatusIds = [
            Status::ITEM_DEFECTIVE,
            Status::ITEM_RETURNED,
            Status::ITEM_REPLACED,
        ];

        $acceptedStatusIds = [
            Status::ITEM_ACCEPTED,
            Status::ITEM_RECORDED,
        ];

        $issueCount = $latestInspectionItems->filter(
            fn (InspectionReportItem $item) => in_array((int) $item->inspection_status_id, $issueStatusIds, true)
        )->count();

        $acceptedCount = $latestInspectionItems->filter(
            fn (InspectionReportItem $item) => in_array((int) $item->inspection_status_id, $acceptedStatusIds, true)
        )->count();

        $pendingCount = max($order->items->count() - $acceptedCount - $issueCount, 0);
        $latestInspectionDate = optional($order->inspectionReports->sortByDesc('inspection_date')->first()?->inspection_date)->format('M d, Y');
        $deliveriesCount = $order->inspectionReports->count();

        return [
            'accepted_count' => $acceptedCount,
            'issue_count' => $issueCount,
            'pending_count' => $pendingCount,
            'deliveries_count' => $deliveriesCount,
            'latest_inspection_date' => $latestInspectionDate,
            'can_inspect' => in_array((int) $order->status_id, [
                Status::PO_PARTIALLY_DELIVERED,
                Status::PO_DELIVERED_PENDING_INSPECTION,
                Status::PO_CLOSED,
            ], true) || $deliveriesCount > 0,
        ];
    }

    protected function notifyStakeholdersOfPurchaseOrder(PurchaseOrder $purchaseOrder): void
    {
        $purchaseOrder->loadMissing(['purchaseRequest', 'purchaseRequest.requester']);

        $requesterId = $purchaseOrder->purchaseRequest?->account_id;
        $custodianIds = Account::where('role', 'custodian')->pluck('account_id');
        $senderId = Auth::id();

        $message = sprintf('Purchase order %s has been generated for request %s.', $purchaseOrder->po_no, $purchaseOrder->pr_no);

        $recipientIds = collect([$requesterId])->merge($custodianIds)->filter()->unique();

        foreach ($recipientIds as $recipientId) {
            Notification::create([
                'recipient_id' => $recipientId,
                'sender_id' => $senderId,
                'table_name' => 'purchase_orders',
                'record_id' => $purchaseOrder->po_no,
                'message' => $message,
                'type' => 'success',
                'is_read' => false,
                'created_at' => now(),
            ]);
        }
    }

    protected function notifyStakeholdersOfReceivingProgress(PurchaseOrder $purchaseOrder, PurchaseOrderItem $item, bool $allReceived): void
    {
        $purchaseOrder->loadMissing(['purchaseRequest', 'purchaseRequest.requester']);

        $requesterId = $purchaseOrder->purchaseRequest?->account_id;
        $stakeholderIds = Account::whereIn('role', ['custodian', 'bac', 'iac', 'admin'])->pluck('account_id');
        $senderId = Auth::id();

        if (! $senderId) {
            return;
        }

        $message = $allReceived
            ? sprintf('All items for purchase order %s have been received and are ready for inspection.', $purchaseOrder->po_no)
            : sprintf('Item "%s" (Qty %d) for purchase order %s has been marked as received.', $item->item_description, $item->quantity, $purchaseOrder->po_no);

        $type = $allReceived ? 'success' : 'info';

    $recipientIds = collect([$requesterId])->merge($stakeholderIds)->filter()->unique();

        foreach ($recipientIds as $recipientId) {
            Notification::create([
                'recipient_id' => $recipientId,
                'sender_id' => $senderId,
                'table_name' => 'purchase_orders',
                'record_id' => $purchaseOrder->po_no,
                'message' => $message,
                'type' => $type,
                'is_read' => false,
                'created_at' => now(),
            ]);
        }
    }

    protected function notifyStakeholdersOfOrderAcceptance(PurchaseOrder $purchaseOrder): void
    {
        $purchaseOrder->loadMissing(['purchaseRequest', 'purchaseRequest.requester']);

        $requesterId = $purchaseOrder->purchaseRequest?->account_id;
        $custodianIds = Account::where('role', 'custodian')->pluck('account_id');
        $senderId = Auth::id();

        $message = sprintf('Purchase order %s has been accepted by the custodian/BAC and sent to the supplier for processing.', $purchaseOrder->po_no);

        $recipientIds = collect([$requesterId])->merge($custodianIds)->filter()->unique();

        foreach ($recipientIds as $recipientId) {
            Notification::create([
                'recipient_id' => $recipientId,
                'sender_id' => $senderId,
                'table_name' => 'purchase_orders',
                'record_id' => $purchaseOrder->po_no,
                'message' => $message,
                'type' => 'success',
                'is_read' => false,
                'created_at' => now(),
            ]);
        }
    }

    protected function notifyRequesterOfItemFulfillment(PurchaseOrder $purchaseOrder, Collection $orderItems): void
    {
        $attentionItems = $orderItems->filter(function (PurchaseOrderItem $item) {
            return $item->fulfillment_status !== 'ordered';
        });

        if ($attentionItems->isEmpty()) {
            return;
        }

        $purchaseOrder->loadMissing(['purchaseRequest', 'purchaseRequest.requester']);

        $requesterId = $purchaseOrder->purchaseRequest?->account_id;
        $senderId = Auth::id();

        if (! $requesterId || ! $senderId) {
            return;
        }

        $lines = $attentionItems->map(function (PurchaseOrderItem $item) {
            if ($item->fulfillment_status === 'alternative') {
                $alternate = $item->alternate_description ?: 'a similar item';
                return sprintf('• %s → substituted with %s (awaiting your decision or timeframe)', $item->item_description, $alternate);
            }

            return sprintf('• %s → currently unavailable. Please let us know how long you can wait so we can revisit sourcing.', $item->item_description);
        })->implode(PHP_EOL);

        Notification::create([
            'recipient_id' => $requesterId,
            'sender_id' => $senderId,
            'table_name' => 'purchase_requests',
            'record_id' => $purchaseOrder->pr_no,
            'message' => sprintf("Updates for PR %s:%s%s", $purchaseOrder->pr_no, PHP_EOL, $lines),
            'type' => 'warning',
            'is_read' => false,
            'created_at' => now(),
        ]);
    }

    protected function notifyRequesterOfItemUpdate(PurchaseOrderItem $item, bool $statusChanged, bool $costChanged, bool $waitChanged = false): void
    {
        $item->loadMissing('purchaseOrder', 'purchaseOrder.purchaseRequest');

        $purchaseOrder = $item->purchaseOrder;
        $purchaseRequest = $purchaseOrder?->purchaseRequest;
        $requesterId = $purchaseRequest?->account_id;
        $senderId = Auth::id();

        if (! $purchaseOrder || ! $requesterId || ! $senderId) {
            return;
        }

        $messageLines = [];
        $notificationType = 'info';

        if ($statusChanged) {
            if ($item->fulfillment_status === 'ordered') {
                $messageLines[] = sprintf('The item "%s" is now marked as ordered.', $item->item_description);
                $notificationType = 'success';
            } elseif ($item->fulfillment_status === 'unavailable') {
                $messageLines[] = sprintf('The item "%s" is currently unavailable.', $item->item_description);
                if ($item->employee_wait_until) {
                    $messageLines[] = $item->employee_wait_until->isPast()
                        ? sprintf('The agreed wait period lapsed on %s.', $item->employee_wait_until->format('M d, Y'))
                        : sprintf('Current agreed wait period runs until %s.', $item->employee_wait_until->format('M d, Y'));
                } else {
                    $messageLines[] = 'Please submit the timeframe you are willing to wait so we can plan a follow-up.';
                }
                $notificationType = 'warning';
            } elseif ($item->fulfillment_status === 'alternative') {
                $messageLines[] = sprintf('A similar item "%s" was proposed for "%s".', $item->alternate_description ?? 'Alternative item', $item->item_description);
                $messageLines[] = 'Please review the purchase request and respond to this option.';
                $notificationType = 'warning';
            }
        }

        if ($costChanged) {
            $messageLines[] = sprintf('Updated unit cost: ₱%s', number_format((float) $item->unit_cost, 2));
        }

        if ($waitChanged && $item->employee_wait_until) {
            $messageLines[] = sprintf('Updated wait timeframe: %s', $item->employee_wait_until->format('M d, Y'));
        }

        if ($item->employee_wait_note) {
            $messageLines[] = sprintf('Employee note: %s', $item->employee_wait_note);
        }

        if (empty($messageLines)) {
            return;
        }

        Notification::create([
            'recipient_id' => $requesterId,
            'sender_id' => $senderId,
            'table_name' => 'purchase_requests',
            'record_id' => $purchaseOrder->pr_no,
            'message' => sprintf(
                "PO %s updates:%s%s",
                $purchaseOrder->po_no,
                PHP_EOL,
                implode(PHP_EOL, $messageLines)
            ),
            'type' => $notificationType,
            'is_read' => false,
            'created_at' => now(),
        ]);
    }

    protected function synchronizeOrderAndRequestStatus(?PurchaseOrder $purchaseOrder): void
    {
        if (! $purchaseOrder) {
            return;
        }

        $purchaseOrder->loadMissing(['items', 'purchaseRequest']);

        if (in_array((int) $purchaseOrder->status_id, [
            Status::PO_PARTIALLY_DELIVERED,
            Status::PO_DELIVERED_PENDING_INSPECTION,
            Status::PO_CLOSED,
            Status::PO_CANCELLED,
        ], true)) {
            return;
        }

        $hasBlockingItems = $purchaseOrder->items->contains(function (PurchaseOrderItem $item) {
            if ($item->fulfillment_status === 'ordered') {
                return false;
            }

            if ($item->fulfillment_status === 'unavailable') {
                $waitUntil = $item->employee_wait_until;
                return !($waitUntil && $waitUntil->isPast());
            }

            return true;
        });

        $targetStatus = $hasBlockingItems ? Status::PO_CREATED : Status::PO_SENT_TO_SUPPLIER;

        $actorId = Auth::id() ?: $purchaseOrder->ordered_by;

        if ($purchaseOrder->status_id !== $targetStatus) {
            $oldStatus = $purchaseOrder->status_id;
            $purchaseOrder->status_id = $targetStatus;
            $purchaseOrder->save();

            StatusHistory::create([
                'table_name' => 'purchase_orders',
                'record_id' => $purchaseOrder->po_no,
                'old_status_id' => $oldStatus,
                'new_status_id' => $targetStatus,
                'changed_by' => $actorId,
                'remarks' => 'Updated after item fulfillment changes.',
                'changed_at' => now(),
            ]);

            PurchaseRequestStatusSynchronizer::sync(
                $purchaseOrder,
                $actorId,
                sprintf('Purchase order %s status adjusted after item updates.', $purchaseOrder->po_no)
            );
        }
    }

    protected function ensurePendingInspectionReport(PurchaseOrder $purchaseOrder): void
    {
        $purchaseOrder->loadMissing([
            'items.latestInspectionItem',
            'inspectionReports.items',
        ]);

        $itemsNeedingInspection = $purchaseOrder->items->filter(function (PurchaseOrderItem $item) {
            return $item->latestInspectionItem === null;
        });

        if ($itemsNeedingInspection->isEmpty()) {
            return;
        }

        $pendingReport = $purchaseOrder->inspectionReports
            ->first(function (InspectionReport $report) {
                return (int) $report->overall_status_id === Status::ITEM_PENDING_INSPECTION;
            });

        if (! $pendingReport) {
            $pendingReport = InspectionReport::create([
                'ia_no' => $this->generateIaNumber(),
                'po_no' => $purchaseOrder->po_no,
                'fund_cluster' => $purchaseOrder->fund_cluster,
                'overall_status_id' => Status::ITEM_PENDING_INSPECTION,
            ]);

            $purchaseOrder->inspectionReports->push($pendingReport);
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
    }

    protected function generateIaNumber(): string
    {
        $datePart = now()->format('Ymd');
        $prefix = 'IA-' . $datePart . '-';

        $last = InspectionReport::where('ia_no', 'like', $prefix . '%')
            ->orderByDesc('ia_no')
            ->lockForUpdate()
            ->first();

        $sequence = 1;

        if ($last) {
            $sequence = (int) Str::afterLast($last->ia_no, '-') + 1;
        }

        return $prefix . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
    }
}
