<?php

namespace App\Http\Controllers\Custodian;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Account;
use App\Models\Notification;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\Status;
use App\Models\StatusHistory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PurchaseRequestController extends Controller
{
    protected function currentUserDivisionId(): ?int
    {
        $accountId = Auth::id();
        if (! $accountId) {
            return null;
        }

        $account = Account::query()
            ->with('employee.section')
            ->find($accountId);

        $employee = $account?->employee;

        return $employee?->section?->division_id
            ? (int) $employee->section->division_id
            : null;
    }

    protected function applyDivisionHeadScope($query, array $context)
    {
        if (($context['role'] ?? null) !== 'division_head') {
            return $query;
        }

        $divisionId = $this->currentUserDivisionId();
        if (! $divisionId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('division_id', $divisionId);
    }

    protected function ensureDivisionHeadCanAccessPurchaseRequest(PurchaseRequest $purchaseRequest, array $context): void
    {
        if (($context['role'] ?? null) !== 'division_head') {
            return;
        }

        $divisionId = $this->currentUserDivisionId();
        if (! $divisionId || (int) $purchaseRequest->division_id !== $divisionId) {
            abort(403, 'You are not allowed to access purchase requests outside your division.');
        }
    }

    protected function workflowContext(): array
    {
        $role = strtolower(Auth::user()?->role ?? session('role', 'custodian'));
        $systemManagedStatuses = Status::purchaseRequestLifecycleStatuses();

        $configs = [
            'division_head' => [
                'role' => 'division_head',
                'route_prefix' => 'division.requests',
                'page_title' => 'Requests for Recommendation',
                'page_description' => 'Division and section heads review submitted purchase requests and provide recommendations.',
                'tabs' => [
                    'for_recommendation' => [
                        'label' => 'For Recommendation',
                        'statuses' => [Status::PR_FOR_RECOMMENDATION],
                    ],
                    'recommended' => [
                        'label' => 'Recommended',
                        'statuses' => [Status::PR_RECOMMENDED],
                    ],
                    'cancelled' => [
                        'label' => 'Cancelled',
                        'statuses' => [Status::PR_CANCELLED],
                    ],
                ],
                'include_all_tab' => true,
                'default_tab' => 'for_recommendation',
                'status_options' => [
                    Status::PR_FOR_RECOMMENDATION,
                    Status::PR_RECOMMENDED,
                    Status::PR_CANCELLED,
                ],
                'transitions' => [
                    Status::PR_FOR_RECOMMENDATION => [Status::PR_RECOMMENDED, Status::PR_CANCELLED],
                    Status::PR_RECOMMENDED => [Status::PR_FOR_RECOMMENDATION, Status::PR_CANCELLED],
                    Status::PR_CANCELLED => [Status::PR_FOR_RECOMMENDATION],
                ],
                'remarks_required_statuses' => [Status::PR_CANCELLED],
                'can_edit_fund_fields' => false,
                'requires_funds_for_approval' => false,
                'approved_status_id' => null,
                'system_managed_statuses' => [],
                'allow_item_editing' => false,
                'for_approval_status_id' => null,
            ],
            'bac' => [
                'role' => 'bac',
                'route_prefix' => 'custodian.requests',
                'page_title' => 'Procurement Queue',
                'page_description' => 'The BAC validates recommended requests, finalizes approvals, and prepares purchase orders.',
                'tabs' => [
                    'for_review' => [
                        'label' => 'For BAC Review',
                        'statuses' => [Status::PR_RECOMMENDED],
                    ],
                    'for_approval' => [
                        'label' => 'For Approval',
                        'statuses' => [Status::PR_FOR_APPROVAL],
                    ],
                    'approved' => [
                        'label' => 'Approved / In Progress',
                        'statuses' => array_merge([Status::PR_APPROVED], $systemManagedStatuses),
                    ],
                    'cancelled' => [
                        'label' => 'Cancelled',
                        'statuses' => [Status::PR_CANCELLED],
                    ],
                ],
                'include_all_tab' => true,
                'default_tab' => 'for_review',
                'status_options' => [
                    Status::PR_FOR_APPROVAL,
                    // Status::PR_APPROVED removed - BAC uses separate approval endpoint
                    Status::PR_RECOMMENDED,
                    Status::PR_CANCELLED,
                    ...$systemManagedStatuses,
                ],
                'transitions' => [
                    Status::PR_RECOMMENDED => [Status::PR_FOR_APPROVAL, Status::PR_CANCELLED],
                    Status::PR_FOR_APPROVAL => [Status::PR_CANCELLED], // Approval removed - use BAC controller
                    Status::PR_APPROVED => [],
                    Status::PR_CANCELLED => [],
                ],
                'remarks_required_statuses' => [Status::PR_CANCELLED],
                'can_edit_fund_fields' => true,
                'requires_funds_for_approval' => true,
                'approved_status_id' => null, // Disabled - BAC uses separate approval workflow
                'system_managed_statuses' => $systemManagedStatuses,
                'allow_item_editing' => true,
                'for_approval_status_id' => Status::PR_FOR_APPROVAL,
            ],
            'custodian' => [
                'role' => 'custodian',
                'route_prefix' => 'custodian.requests',
                'page_title' => 'Procurement Queue',
                'page_description' => 'Custodians manage approved purchase requests and generate purchase orders.',
                'tabs' => [
                    'for_review' => [
                        'label' => 'For BAC Review',
                        'statuses' => [Status::PR_RECOMMENDED],
                    ],
                    'for_approval' => [
                        'label' => 'For Approval',
                        'statuses' => [Status::PR_FOR_APPROVAL],
                    ],
                    'approved' => [
                        'label' => 'Approved / In Progress',
                        'statuses' => array_merge([Status::PR_APPROVED], $systemManagedStatuses),
                    ],
                    'cancelled' => [
                        'label' => 'Cancelled',
                        'statuses' => [Status::PR_CANCELLED],
                    ],
                ],
                'include_all_tab' => true,
                'default_tab' => 'all',
                'status_options' => [],
                'transitions' => [],
                'remarks_required_statuses' => [],
                'can_edit_fund_fields' => false,
                'requires_funds_for_approval' => false,
                'approved_status_id' => null,
                'system_managed_statuses' => $systemManagedStatuses,
                'allow_item_editing' => false,
                'for_approval_status_id' => null,
            ],
        ];

        $default = $configs['bac'];
        $default['role'] = 'bac';

        $context = $configs[$role] ?? $default;
        $context['role'] = $context['role'] ?? $role;
        $context['include_all_tab'] = $context['include_all_tab'] ?? true;
        $context['route_prefix'] = $context['route_prefix'] ?? 'custodian.requests';
        $context['routes'] = [
            'index' => $context['route_prefix'] . '.index',
            'show' => $context['route_prefix'] . '.show',
            'update' => $context['route_prefix'] . '.update-status',
        ];

        if (! isset($context['routes']['item_update']) && ($context['allow_item_editing'] ?? false)) {
            $context['routes']['item_update'] = 'custodian.requests.items.update';
        }

        if (! isset($context['default_tab'])) {
            $context['default_tab'] = array_key_first($context['tabs']) ?? 'all';
        }

        return $context;
    }

    public function index(Request $request)
    {
        $context = $this->workflowContext();
        $tabs = $context['tabs'] ?? [];
        $includeAll = $context['include_all_tab'] ?? true;

        if ($includeAll) {
            $tabs = array_merge(['all' => ['label' => 'All', 'statuses' => null]], $tabs);
        }

        $defaultTab = $context['default_tab'] ?? array_key_first($tabs) ?? 'all';
        $activeTab = $request->query('tab', $defaultTab);
        if (! array_key_exists($activeTab, $tabs)) {
            $activeTab = $defaultTab;
        }

        $search = trim((string) $request->query('search', ''));
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);

        $trackedStatusIds = collect($tabs)
            ->pluck('statuses')
            ->filter(fn ($statuses) => is_array($statuses))
            ->flatten()
            ->map(fn ($statusId) => (int) $statusId)
            ->unique()
            ->values()
            ->all();

        $query = PurchaseRequest::with(['status', 'requester', 'division', 'section'])
            ->orderByDesc('created_at');
        $this->applyDivisionHeadScope($query, $context);

        $selectedStatuses = $tabs[$activeTab]['statuses'] ?? null;
        if (is_array($selectedStatuses)) {
            $query->whereIn('status_id', $selectedStatuses);
        } elseif (! empty($trackedStatusIds)) {
            $query->whereIn('status_id', $trackedStatusIds);
        }

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('pr_no', 'like', "%{$search}%")
                    ->orWhereHas('requester', function ($requesterQuery) use ($search) {
                        $requesterQuery->where('username', 'like', "%{$search}%");
                    })
                    ->orWhereHas('division', function ($divisionQuery) use ($search) {
                        $divisionQuery->where('division_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('section', function ($sectionQuery) use ($search) {
                        $sectionQuery->where('section_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('status', function ($statusQuery) use ($search) {
                        $statusQuery->where('status_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($dateFrom && $dateTo) {
            $query->whereBetween('created_at', [$dateFrom.' 00:00:00', $dateTo.' 23:59:59']);
        }

        $purchaseRequests = $query->paginate(5)->withQueryString();

        $statusCountsQuery = PurchaseRequest::select('status_id', DB::raw('COUNT(*) as aggregate'));
        $this->applyDivisionHeadScope($statusCountsQuery, $context);
        if (! empty($trackedStatusIds)) {
            $statusCountsQuery->whereIn('status_id', $trackedStatusIds);
        }

        $statusCounts = $statusCountsQuery
            ->groupBy('status_id')
            ->pluck('aggregate', 'status_id');

        $tabCounts = [];
        foreach ($tabs as $key => $definition) {
            $statusSet = $definition['statuses'] ?? null;
            if (is_array($statusSet) && ! empty($statusSet)) {
                $tabCounts[$key] = collect($statusSet)->sum(
                    fn (int $statusId) => (int) ($statusCounts[$statusId] ?? 0)
                );
            } else {
                $tabCounts[$key] = ! empty($trackedStatusIds)
                    ? (int) $this->applyDivisionHeadScope(PurchaseRequest::whereIn('status_id', $trackedStatusIds), $context)->count()
                    : (int) $this->applyDivisionHeadScope(PurchaseRequest::query(), $context)->count();
            }
        }

        $statusOptionIds = $context['status_options'] ?? $trackedStatusIds;
        $statuses = empty($statusOptionIds)
            ? collect()
            : Status::query()
                ->whereIn('status_id', $statusOptionIds)
                ->orderBy('status_name')
                ->get();

        $user = Auth::user();
        $currentUserName = $user->employee ? collect([
            $user->employee->first_name,
            $user->employee->middle_name,
            $user->employee->last_name,
            $user->employee->suffix,
        ])->filter()->implode(' ') : $user->name;

        $tabLabels = collect($tabs)->mapWithKeys(fn ($definition, $key) => [$key => $definition['label'] ?? ucfirst(str_replace('_', ' ', $key))])->toArray();
        $tabStatusMap = collect($tabs)->mapWithKeys(function ($definition, $key) {
            $statuses = $definition['statuses'] ?? [];

            if (! is_array($statuses)) {
                return [$key => []];
            }

            return [$key => collect($statuses)->map(fn ($statusId) => (int) $statusId)->values()->all()];
        })->toArray();

        $workflowConfig = [
            'statuses' => $statuses->map(fn ($status) => [
                'id' => $status->status_id,
                'name' => $status->status_name,
            ])->values(),
            'currentUserName' => $currentUserName,
            'showUrlTemplate' => route($context['routes']['show'], ['purchase_request' => '__PR__']),
            'updateUrlTemplate' => route($context['routes']['update'], ['purchase_request' => '__PR__']),
            'approvedStatusId' => $context['approved_status_id'],
            'declinedStatusId' => Status::PR_CANCELLED,
            'remarksRequiredStatuses' => $context['remarks_required_statuses'] ?? [Status::PR_CANCELLED],
            'canEditFunds' => $context['can_edit_fund_fields'] ?? false,
            'requiresFundsForApproval' => $context['requires_funds_for_approval'] ?? false,
            'role' => $context['role'],
            'systemManagedStatuses' => $context['system_managed_statuses'] ?? [],
            'transitions' => collect($context['transitions'] ?? [])->mapWithKeys(fn ($targets, $source) => [
                (string) $source => collect($targets)->map(fn ($target) => (string) $target)->all(),
            ])->toArray(),
            'allowItemEditing' => (bool) ($context['allow_item_editing'] ?? false),
            'itemUpdateUrlTemplate' => ($context['allow_item_editing'] ?? false)
                ? route($context['routes']['item_update'] ?? 'custodian.requests.items.update', ['purchase_request_item' => '__PRI__'])
                : null,
            'forApprovalStatusId' => $context['for_approval_status_id'] ?? null,
        ];

        return view('custodian.purchase_requests.index', [
            'purchaseRequests' => $purchaseRequests,
            'statuses' => $statuses,
            'currentUserName' => $currentUserName,
            'activeTab' => $activeTab,
            'defaultTab' => $defaultTab,
            'tabCounts' => $tabCounts,
            'tabs' => $tabLabels,
            'tabStatusMap' => $tabStatusMap,
            'routes' => $context['routes'],
            'routeParam' => 'purchase_request',
            'pageTitle' => $context['page_title'] ?? 'Purchase Requests Queue',
            'pageDescription' => $context['page_description'] ?? 'Review employee submissions and manage their status lifecycle.',
            'search' => $search,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'canEditFunds' => $context['can_edit_fund_fields'] ?? false,
            'workflowRole' => $context['role'],
            'workflowConfig' => $workflowConfig,
            'remarksRequiredStatuses' => $context['remarks_required_statuses'] ?? [Status::PR_CANCELLED],
            'systemManagedStatuses' => $context['system_managed_statuses'] ?? [],
        ]);
    }

    public function show(Request $request, PurchaseRequest $purchaseRequest)
    {
        $context = $this->workflowContext();
        $this->ensureDivisionHeadCanAccessPurchaseRequest($purchaseRequest, $context);

        $purchaseRequest->load([
            'items',
            'status',
            'requester.employee',
            'division',
            'section',
            'approver.employee',
            'recommender.employee',
            'statusHistory' => function ($query) {
                $query->orderByDesc('changed_at')->limit(1);
            },
            'reviewer',
        ]);

        $latestHistory = $purchaseRequest->statusHistory->first();
        $requesterSignature = $this->signatureDataUri($purchaseRequest->requester?->employee?->signature);
        $recommendedSignature = $this->signatureDataUri($purchaseRequest->recommender?->employee?->signature);
        $approvedSignature = $this->signatureDataUri($purchaseRequest->approver?->employee?->signature);

        if ($request->wantsJson()) {
            $requesterSignature = $this->signatureDataUri($purchaseRequest->requester?->employee?->signature);
            $recommendedSignature = $this->signatureDataUri($purchaseRequest->recommender?->employee?->signature);
            $approvedSignature = $this->signatureDataUri($purchaseRequest->approver?->employee?->signature);

            return response()->json([
                'data' => [
                    'pr_no' => $purchaseRequest->pr_no,
                    'status' => $purchaseRequest->status?->status_name,
                    'status_id' => $purchaseRequest->status_id,
                    'purpose' => $purchaseRequest->purpose,
                    'sai_no' => $purchaseRequest->sai_no,
                    'alobs_no' => $purchaseRequest->alobs_no,
                    'fund_cluster' => $purchaseRequest->fund_cluster,
                    'funds_available' => $purchaseRequest->funds_available,
                    'division' => $purchaseRequest->division?->division_name,
                    'section' => $purchaseRequest->section?->section_name,
                    'requester' => $purchaseRequest->requester->employee ? collect([
                        $purchaseRequest->requester->employee->first_name,
                        $purchaseRequest->requester->employee->middle_name,
                        $purchaseRequest->requester->employee->last_name,
                        $purchaseRequest->requester->employee->suffix,
                    ])->filter()->implode(' ') : 'Unknown',
                    'requester_signature' => $requesterSignature,
                    'approved_by' => $purchaseRequest->approver ? collect([
                        $purchaseRequest->approver->employee->first_name ?? null,
                        $purchaseRequest->approver->employee->middle_name ?? null,
                        $purchaseRequest->approver->employee->last_name ?? null,
                        $purchaseRequest->approver->employee->suffix ?? null,
                    ])->filter()->implode(' ') : null,
                    'approved_signature' => $approvedSignature,
                    'created_at' => optional($purchaseRequest->created_at)->toDateTimeString(),
                    'recommended_at' => optional($purchaseRequest->recommended_at)->toDateTimeString(),
                    'recommendation_remarks' => $purchaseRequest->recommendation_remarks,
                    'total_estimated_cost' => $purchaseRequest->total_estimated_cost,
                    'latest_status_remarks' => $latestHistory?->remarks,
                    'latest_status_changed_at' => optional($latestHistory?->changed_at)->toDateTimeString(),
                    'approval_remarks' => $purchaseRequest->approval_remarks,
                    'recommended_by' => $purchaseRequest->recommended_by,
                    'recommended_by_name' => $purchaseRequest->recommender
                        ? ($purchaseRequest->recommender->employee
                            ? collect([
                                $purchaseRequest->recommender->employee->first_name ?? null,
                                $purchaseRequest->recommender->employee->middle_name ?? null,
                                $purchaseRequest->recommender->employee->last_name ?? null,
                                $purchaseRequest->recommender->employee->suffix ?? null,
                            ])->filter()->implode(' ')
                            : $purchaseRequest->recommender->username)
                        : null,
                    'recommended_signature' => $recommendedSignature,
                    'items' => $purchaseRequest->items->map(fn (PurchaseRequestItem $item) => $this->transformItem($item)),
                    'can_edit_items' => (int) $purchaseRequest->status_id === Status::PR_FOR_APPROVAL,
                ],
            ]);
        }

        return view('custodian.purchase_requests.show', compact('purchaseRequest'));
    }

    public function updateStatus(Request $request, PurchaseRequest $purchaseRequest)
    {
        $context = $this->workflowContext();
        $this->ensureDivisionHeadCanAccessPurchaseRequest($purchaseRequest, $context);

        $canEditFunds = $context['can_edit_fund_fields'] ?? false;
        $remarksRequiredStatuses = $context['remarks_required_statuses'] ?? [Status::PR_CANCELLED];
        $allowedTransitions = $context['transitions'] ?? [];

        $rules = [
            'status_id' => [
                'required',
            ],
            'remarks' => 'nullable|string|max:1000',
        ];

        if ($canEditFunds) {
            $rules['fund_cluster'] = 'nullable|string|max:50';
            $rules['funds_available'] = 'nullable|numeric|min:0';
        }

        $validated = $request->validate([
            ...$rules,
        ]);

        $targetStatus = (int) $validated['status_id'];
        $remarks = trim((string) ($validated['remarks'] ?? ''));
        $accountId = Auth::id();

        if (! $accountId) {
            abort(403, 'Authenticated account required.');
        }

        $currentStatus = (int) $purchaseRequest->status_id;
        $statusChanged = $targetStatus !== $currentStatus;

        // Prevent a reviewer from approving their own request to avoid conflicts of interest
        if ($statusChanged && $targetStatus === Status::PR_APPROVED && $purchaseRequest->account_id === $accountId) {
            throw ValidationException::withMessages([
                'status_id' => 'You cannot approve a purchase request you submitted. Please have another authorized approver review this request.',
            ]);
        }

        if ($statusChanged && in_array($targetStatus, $remarksRequiredStatuses, true) && $remarks === '') {
            throw ValidationException::withMessages([
                'remarks' => 'Please provide remarks for this status update.',
            ]);
        }

        if ($statusChanged && ! in_array($targetStatus, $allowedTransitions[$currentStatus] ?? [], true)) {
            throw ValidationException::withMessages([
                'status_id' => 'The requested status transition is not allowed for this purchase request.',
            ]);
        }

        if ($statusChanged && $targetStatus === Status::PR_APPROVED) {
            $purchaseRequest->loadMissing('items');

            $blockingItems = $purchaseRequest->items->filter(function (PurchaseRequestItem $item) {
                if ($item->employee_decision === 'accept') {
                    return $item->fulfillment_status !== 'alternative' || $item->alternate_description === null || $item->alternate_description === '';
                }

                if ($item->employee_decision === 'wait') {
                    return ! $item->employee_wait_until;
                }

                return false;
            })->values();

            if ($blockingItems->isNotEmpty()) {
                $firstItem = $blockingItems->first();
                $itemLabel = $firstItem ? $firstItem->item_description : 'some items';

                throw ValidationException::withMessages([
                    'status_id' => sprintf(
                        'Cannot approve yet. %s still requires the requester to proceed with the proposed change or provide a valid wait timeframe.',
                        $itemLabel
                    ),
                ]);
            }
        }

        $fundCluster = $canEditFunds && array_key_exists('fund_cluster', $validated)
            ? $validated['fund_cluster']
            : null;

        $fundsAvailableInput = $canEditFunds && array_key_exists('funds_available', $validated)
            ? $validated['funds_available']
            : null;

        DB::transaction(function () use (
            $purchaseRequest,
            $targetStatus,
            $accountId,
            $remarks,
            $fundCluster,
            $fundsAvailableInput,
            $statusChanged,
            $context,
            $canEditFunds
        ) {
            $oldStatus = (int) $purchaseRequest->status_id;
            $now = now();

            if ($canEditFunds && $fundCluster !== null) {
                $purchaseRequest->fund_cluster = $fundCluster === '' ? null : $fundCluster;
            }

            if ($canEditFunds && $fundsAvailableInput !== null) {
                $purchaseRequest->funds_available = $fundsAvailableInput;
            }

            switch ($targetStatus) {
                case Status::PR_FOR_RECOMMENDATION:
                    $purchaseRequest->status_id = $targetStatus;
                    $purchaseRequest->recommended_by = null;
                    $purchaseRequest->recommended_at = null;
                    $purchaseRequest->recommendation_remarks = null;
                    $purchaseRequest->approved_by = null;
                    $purchaseRequest->approved_at = null;
                    $purchaseRequest->approval_remarks = null;
                    break;

                case Status::PR_RECOMMENDED:
                    $purchaseRequest->status_id = $targetStatus;
                    $purchaseRequest->recommended_by = $accountId;
                    $purchaseRequest->recommended_at = $now;
                    if ($remarks !== '') {
                        $purchaseRequest->recommendation_remarks = $remarks;
                    }
                    $purchaseRequest->approved_by = null;
                    $purchaseRequest->approved_at = null;
                    $purchaseRequest->approval_remarks = null;
                    break;

                case Status::PR_FOR_APPROVAL:
                    if (! $purchaseRequest->recommended_by) {
                        $purchaseRequest->recommended_by = $accountId;
                        $purchaseRequest->recommended_at = $now;
                    }
                    if ($remarks !== '') {
                        $purchaseRequest->recommendation_remarks = $remarks;
                    }
                    $purchaseRequest->status_id = $targetStatus;
                    break;

                case Status::PR_APPROVED:
                    // APPROVAL IS RESTRICTED TO BAC ONLY
                    // This controller is for custodians; approvals must go through BAC\PurchaseRequestController
                    throw ValidationException::withMessages([
                        'status_id' => 'Purchase request approval is restricted to BAC members only. Please use the BAC approval workflow.',
                    ]);

                    break;

                case Status::PR_CANCELLED:
                    $purchaseRequest->status_id = $targetStatus;
                    $purchaseRequest->approval_remarks = $remarks !== '' ? $remarks : null;
                    $purchaseRequest->approved_by = null;
                    $purchaseRequest->approved_at = null;
                    break;

                default:
                    $purchaseRequest->status_id = $targetStatus;
                    break;
            }

            $purchaseRequest->reviewed_by = $accountId;
            $purchaseRequest->save();

            if ($statusChanged) {
                StatusHistory::create([
                    'table_name' => 'purchase_requests',
                    'record_id' => $purchaseRequest->pr_no,
                    'old_status_id' => $oldStatus,
                    'new_status_id' => $targetStatus,
                    'changed_by' => $accountId,
                    'remarks' => $remarks !== '' ? $remarks : null,
                    'changed_at' => $now,
                ]);

                AuditLog::create([
                    'account_id' => $accountId,
                    'table_name' => 'purchase_requests',
                    'action' => 'STATUS_UPDATE',
                    'description' => sprintf(
                        'Updated purchase request %s status from %s to %s',
                        $purchaseRequest->pr_no,
                        $oldStatus,
                        $targetStatus
                    ),
                    'log_time' => $now,
                ]);
            }
        });

        $purchaseRequest->refresh();

        if ($statusChanged) {
            $this->notifyRequesterOfStatusChange($purchaseRequest, $targetStatus, $accountId, $remarks);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Purchase request updated successfully.',
        ]);
    }

    public function updateItem(Request $request, PurchaseRequestItem $purchaseRequestItem)
    {
        $purchaseRequestItem->loadMissing('purchaseRequest');
        $purchaseRequest = $purchaseRequestItem->purchaseRequest;

        if (! $purchaseRequest) {
            abort(404, 'Purchase request not found for the selected item.');
        }

        if ((int) $purchaseRequest->status_id !== Status::PR_FOR_APPROVAL) {
            return response()->json([
                'status' => 'error',
                'message' => 'Item updates are only allowed while the request is in the “For Approval” stage.',
            ], 422);
        }

        $validated = $request->validate([
            'fulfillment_status' => ['required', Rule::in(['pending', 'ordered', 'unavailable', 'alternative'])],
            'alternate_description' => ['nullable', 'string', 'max:255'],
            'estimated_unit_cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        $status = $validated['fulfillment_status'];
        $alternateDescription = trim((string) ($validated['alternate_description'] ?? ''));

        if ($status === 'alternative' && $alternateDescription === '') {
            throw ValidationException::withMessages([
                'alternate_description' => 'Please describe the proposed alternative item.',
            ]);
        }

        $previousStatus = $purchaseRequestItem->fulfillment_status;
        $previousAlternate = $purchaseRequestItem->alternate_description;
        $previousCost = (float) $purchaseRequestItem->estimated_unit_cost;

        $updatedItem = DB::transaction(function () use (
            $purchaseRequestItem,
            $purchaseRequest,
            $status,
            $alternateDescription,
            $validated,
            $previousStatus
        ) {
            $unitCost = array_key_exists('estimated_unit_cost', $validated)
                ? (float) ($validated['estimated_unit_cost'] ?? 0)
                : (float) $purchaseRequestItem->estimated_unit_cost;

            if ($status === 'unavailable') {
                $unitCost = 0;
            }

            $purchaseRequestItem->fulfillment_status = $status;
            $purchaseRequestItem->alternate_description = $status === 'alternative' ? $alternateDescription : null;
            $purchaseRequestItem->estimated_unit_cost = $unitCost;

            if ($previousStatus !== $status) {
                $purchaseRequestItem->employee_decision = null;
                $purchaseRequestItem->employee_decided_at = null;
                $purchaseRequestItem->employee_wait_until = null;
                $purchaseRequestItem->employee_wait_note = null;
            }

            $purchaseRequestItem->save();

            AuditLog::create([
                'account_id' => Auth::id(),
                'table_name' => 'purchase_request_items',
                'action' => 'UPDATE',
                'description' => sprintf(
                    'Updated PR %s item %d fulfillment status to %s',
                    $purchaseRequest->pr_no,
                    $purchaseRequestItem->pri_id,
                    $status
                ),
                'log_time' => now(),
            ]);

            return $purchaseRequestItem->fresh();
        });

        if ($previousStatus !== $updatedItem->fulfillment_status || $previousAlternate !== $updatedItem->alternate_description || abs($previousCost - (float) $updatedItem->estimated_unit_cost) > 0.009) {
            $this->notifyRequesterOfItemChange($updatedItem, $previousStatus);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Item fulfillment details updated successfully.',
            'data' => [
                'item' => $this->transformItem($updatedItem),
            ],
        ]);
    }

    protected function resolveFulfillmentStatusFromDecision(PurchaseRequestItem $item): string
    {
        if ($item->employee_decision === 'accept' && $item->alternate_description) {
            return 'alternative';
        }

        if ($item->employee_decision === 'wait') {
            return 'ordered';
        }

        return 'ordered';
    }

    protected function autoAlignItemStatuses(PurchaseRequest $purchaseRequest, int $accountId, Carbon $timestamp): void
    {
        if (! $purchaseRequest->relationLoaded('items')) {
            $purchaseRequest->load('items');
        }

        $changedItems = [];

        foreach ($purchaseRequest->items as $item) {
            $targetStatus = $this->resolveFulfillmentStatusFromDecision($item);

            if ($item->fulfillment_status === $targetStatus) {
                continue;
            }

            $previousStatus = $item->fulfillment_status ?? 'pending';
            $item->fulfillment_status = $targetStatus;

            if ($targetStatus !== 'alternative') {
                $item->alternate_description = null;
            }

            $item->save();

            $changedItems[] = [$item, $previousStatus];
        }

        foreach ($changedItems as [$item, $previousStatus]) {
            AuditLog::create([
                'account_id' => $accountId,
                'table_name' => 'purchase_request_items',
                'action' => 'STATUS_UPDATE',
                'description' => sprintf(
                    'Automatically aligned PR %s item %d status from %s to %s based on requester response.',
                    $purchaseRequest->pr_no,
                    $item->pri_id,
                    $previousStatus,
                    $item->fulfillment_status
                ),
                'log_time' => $timestamp,
            ]);
        }
    }

    protected function transformItem(PurchaseRequestItem $item): array
    {
        return [
            'pri_id' => $item->pri_id,
            'pr_no' => $item->pr_no,
            'item_description' => $item->item_description,
            'item_type' => $item->item_type,
            'quantity' => $item->quantity,
            'unit' => $item->unit,
            'stock_number' => $item->stock_number,
            'estimated_unit_cost' => $item->estimated_unit_cost,
            'estimated_total_cost' => $item->estimated_total_cost,
            'remarks' => $item->remarks,
            'fulfillment_status' => $item->fulfillment_status,
            'alternate_description' => $item->alternate_description,
            'employee_decision' => $item->employee_decision,
            'employee_decided_at' => optional($item->employee_decided_at)->toDateTimeString(),
            'employee_wait_until' => optional($item->employee_wait_until)->toDateString(),
            'employee_wait_note' => $item->employee_wait_note,
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

    protected function notifyRequesterOfItemChange(PurchaseRequestItem $item, ?string $previousStatus = null): void
    {
        $item->loadMissing('purchaseRequest', 'purchaseRequest.requester');
        $purchaseRequest = $item->purchaseRequest;

        if (! $purchaseRequest) {
            return;
        }

        $requesterId = $purchaseRequest->account_id;
        $senderId = Auth::id();

        if (! $requesterId || ! $senderId) {
            return;
        }

        $statusLabel = match ($item->fulfillment_status) {
            'alternative' => 'marked with a proposed alternative',
            'unavailable' => 'tagged as temporarily unavailable',
            'ordered', 'pending' => 'reverted to the originally requested specification',
            default => 'updated',
        };

        $lines = [
            sprintf('Item "%s" on PR %s was %s.', $item->item_description, $purchaseRequest->pr_no, $statusLabel),
        ];

        if ($item->fulfillment_status === 'alternative' && $item->alternate_description) {
            $lines[] = 'Proposed alternative: '.$item->alternate_description;
        }

        if ($item->fulfillment_status === 'unavailable') {
            $lines[] = 'Please advise if you prefer to wait or proceed with an alternative item.';
        }

        $type = match ($item->fulfillment_status) {
            'alternative', 'unavailable' => 'warning',
            default => 'info',
        };

        Notification::create([
            'recipient_id' => $requesterId,
            'sender_id' => $senderId,
            'table_name' => 'purchase_requests',
            'record_id' => $purchaseRequest->pr_no,
            'message' => implode(PHP_EOL, $lines),
            'type' => $type,
            'is_read' => false,
            'created_at' => now(),
        ]);
    }

    protected function notifyRequesterOfStatusChange(PurchaseRequest $purchaseRequest, int $statusId, int $accountId, string $remarks = ''): void
    {
        $status = Status::find($statusId);
        $statusName = $status?->status_name ?? 'updated';

        $lines = [
            sprintf('Your purchase request %s status changed to %s.', $purchaseRequest->pr_no, $statusName),
        ];

        if ($remarks !== '') {
            $lines[] = 'Reason: '.$remarks;
        }

        $type = match ($statusId) {
            Status::PR_APPROVED => 'success',
            Status::PR_CANCELLED => 'warning',
            default => 'info',
        };

        Notification::create([
            'recipient_id' => $purchaseRequest->account_id,
            'sender_id' => $accountId,
            'table_name' => 'purchase_requests',
            'record_id' => $purchaseRequest->pr_no,
            'message' => implode(PHP_EOL, $lines),
            'type' => $type,
            'is_read' => false,
            'created_at' => now(),
        ]);
    }

    public function printPdf(Request $request): View
    {
        $context = $this->workflowContext();
        $tabs = $context['tabs'] ?? [];
        $includeAll = $context['include_all_tab'] ?? true;

        if ($includeAll) {
            $tabs = array_merge(['all' => ['label' => 'All', 'statuses' => null]], $tabs);
        }

        $defaultTab = $context['default_tab'] ?? array_key_first($tabs) ?? 'all';
        $activeTab = $request->query('tab', $defaultTab);
        if (! array_key_exists($activeTab, $tabs)) {
            $activeTab = $defaultTab;
        }

        $search = trim((string) $request->query('search', ''));
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);

        $trackedStatusIds = collect($tabs)
            ->pluck('statuses')
            ->filter(fn ($statuses) => is_array($statuses))
            ->flatten()
            ->map(fn ($statusId) => (int) $statusId)
            ->unique()
            ->values()
            ->all();

        $query = PurchaseRequest::with(['status', 'requester', 'division', 'section', 'items'])
            ->orderByDesc('created_at');

        $selectedStatuses = $tabs[$activeTab]['statuses'] ?? null;
        if (is_array($selectedStatuses)) {
            $query->whereIn('status_id', $selectedStatuses);
        } elseif (! empty($trackedStatusIds)) {
            $query->whereIn('status_id', $trackedStatusIds);
        }

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('pr_no', 'like', "%{$search}%")
                    ->orWhereHas('requester', function ($requesterQuery) use ($search) {
                        $requesterQuery->where('username', 'like', "%{$search}%");
                    })
                    ->orWhereHas('division', function ($divisionQuery) use ($search) {
                        $divisionQuery->where('division_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('section', function ($sectionQuery) use ($search) {
                        $sectionQuery->where('section_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('status', function ($statusQuery) use ($search) {
                        $statusQuery->where('status_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($dateFrom && $dateTo) {
            $query->whereBetween('created_at', [$dateFrom.' 00:00:00', $dateTo.' 23:59:59']);
        }

        $purchaseRequests = $query->get();

        $account = Auth::user();
        $preparedByName = $account?->employee?->full_name
            ?: $account?->username
            ?: 'System User';
        $preparedByRole = $account?->role
            ? ucfirst(str_replace('_', ' ', (string) $account->role))
            : 'User';

        return view('custodian.purchase_requests.print', [
            'purchaseRequests' => $purchaseRequests,
            'activeTab' => $activeTab,
            'pageTitle' => $context['page_title'] ?? 'Purchase Requests Queue',
            'generatedOnLabel' => $this->buildGeneratedOnLabel($dateFrom, $dateTo),
            'preparedByName' => $preparedByName,
            'preparedByRole' => $preparedByRole,
        ]);
    }

    public function exportExcel(Request $request): Response
    {
        $context = $this->workflowContext();
        $tabs = $context['tabs'] ?? [];
        $includeAll = $context['include_all_tab'] ?? true;

        if ($includeAll) {
            $tabs = array_merge(['all' => ['label' => 'All', 'statuses' => null]], $tabs);
        }

        $defaultTab = $context['default_tab'] ?? array_key_first($tabs) ?? 'all';
        $activeTab = $request->query('tab', $defaultTab);
        if (! array_key_exists($activeTab, $tabs)) {
            $activeTab = $defaultTab;
        }

        $search = trim((string) $request->query('search', ''));
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);

        $trackedStatusIds = collect($tabs)
            ->pluck('statuses')
            ->filter(fn ($statuses) => is_array($statuses))
            ->flatten()
            ->map(fn ($statusId) => (int) $statusId)
            ->unique()
            ->values()
            ->all();

        $query = PurchaseRequest::with(['status', 'requester', 'division', 'section', 'items'])
            ->orderByDesc('created_at');

        $selectedStatuses = $tabs[$activeTab]['statuses'] ?? null;
        if (is_array($selectedStatuses)) {
            $query->whereIn('status_id', $selectedStatuses);
        } elseif (! empty($trackedStatusIds)) {
            $query->whereIn('status_id', $trackedStatusIds);
        }

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('pr_no', 'like', "%{$search}%")
                    ->orWhereHas('requester', function ($requesterQuery) use ($search) {
                        $requesterQuery->where('username', 'like', "%{$search}%");
                    })
                    ->orWhereHas('division', function ($divisionQuery) use ($search) {
                        $divisionQuery->where('division_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('section', function ($sectionQuery) use ($search) {
                        $sectionQuery->where('section_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('status', function ($statusQuery) use ($search) {
                        $statusQuery->where('status_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($dateFrom && $dateTo) {
            $query->whereBetween('created_at', [$dateFrom.' 00:00:00', $dateTo.' 23:59:59']);
        }

        $purchaseRequests = $query->get();

        $account = Auth::user();
        $preparedByName = $account?->employee?->full_name
            ?: $account?->username
            ?: 'System User';
        $preparedByRole = $account?->role
            ? ucfirst(str_replace('_', ' ', (string) $account->role))
            : 'User';

        $html = view('custodian.purchase_requests.excel', [
            'purchaseRequests' => $purchaseRequests,
            'generatedOnLabel' => $this->buildGeneratedOnLabel($dateFrom, $dateTo),
            'preparedByName' => $preparedByName,
            'preparedByRole' => $preparedByRole,
        ])->render();

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="Purchase-Requests-' . date('Y-m-d') . '.xls"');
    }

    private function resolveDateRange(Request $request): array
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

    private function buildGeneratedOnLabel(?string $dateFrom, ?string $dateTo): string
    {
        if ($dateFrom && $dateTo) {
            return Carbon::parse($dateFrom)->format('F d, Y').' - '.Carbon::parse($dateTo)->format('F d, Y');
        }

        return now()->format('F d, Y');
    }
}
