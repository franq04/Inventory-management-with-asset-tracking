<?php

namespace App\Http\Controllers\BAC;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\PurchaseRequest;
use App\Models\Status;
use App\Models\StatusHistory;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * BAC (Bids and Awards Committee) Controller
 * 
 * This controller handles the BAC-specific approval workflow.
 * BAC is the ONLY role authorized to give final approval on purchase requests.
 * 
 * Workflow:
 * 1. PR with status "Recommended" (103) arrives from Division Head
 * 2. BAC reviews and moves to "For BAC Approval" (104)
 * 3. BAC gives "Final Approval" (105) or "Cancels" (106)
 */
class PurchaseRequestController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display purchase requests pending BAC action.
     */
    public function index(Request $request)
    {
        // Verify BAC role using Gate
        if (Gate::denies('bac-approve')) {
            abort(403, 'Only BAC members can access this page.');
        }

        $tabs = [
            'recommended' => [
                'label' => 'Awaiting BAC Review',
                'statuses' => [Status::PR_RECOMMENDED],
            ],
            'for_approval' => [
                'label' => 'For Final Approval',
                'statuses' => [Status::PR_FOR_APPROVAL],
            ],
            'approved' => [
                'label' => 'Approved',
                'statuses' => [Status::PR_APPROVED],
            ],
            'cancelled' => [
                'label' => 'Cancelled',
                'statuses' => [Status::PR_CANCELLED],
            ],
        ];

        $activeTab = $request->query('tab', 'recommended');
        if (!array_key_exists($activeTab, $tabs)) {
            $activeTab = 'recommended';
        }

        $trackedStatusIds = collect($tabs)
            ->pluck('statuses')
            ->flatten()
            ->unique()
            ->values()
            ->all();

        $query = PurchaseRequest::with(['status', 'requester.employee', 'division', 'section', 'recommender.employee'])
            ->orderByDesc('created_at');

        $selectedStatuses = $tabs[$activeTab]['statuses'] ?? null;
        if (is_array($selectedStatuses)) {
            $query->whereIn('status_id', $selectedStatuses);
        }

        $purchaseRequests = $query->paginate(10)->withQueryString();

        // Count for each tab
        $tabCounts = [];
        foreach ($tabs as $key => $definition) {
            $tabCounts[$key] = PurchaseRequest::whereIn('status_id', $definition['statuses'])->count();
        }

        $statuses = Status::whereIn('status_id', [
            Status::PR_RECOMMENDED,
            Status::PR_FOR_APPROVAL,
            Status::PR_APPROVED,
            Status::PR_CANCELLED,
        ])->get();

        return view('bac.purchase_requests.index', [
            'purchaseRequests' => $purchaseRequests,
            'statuses' => $statuses,
            'activeTab' => $activeTab,
            'tabCounts' => $tabCounts,
            'tabs' => collect($tabs)->mapWithKeys(fn ($def, $key) => [$key => $def['label']])->toArray(),
        ]);
    }

    /**
     * Display a single purchase request for BAC review.
     */
    public function show(Request $request, PurchaseRequest $purchaseRequest)
    {
        if (Gate::denies('bac-approve')) {
            abort(403, 'Only BAC members can access this page.');
        }

        $purchaseRequest->load([
            'items',
            'status',
            'requester.employee',
            'division',
            'section',
            'recommender.employee',
            'approver.employee',
            'statusHistory' => fn ($q) => $q->orderByDesc('changed_at'),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'data' => $this->transformPurchaseRequest($purchaseRequest),
            ]);
        }

        return view('bac.purchase_requests.show', compact('purchaseRequest'));
    }

    /**
     * BAC Final Approval Action
     * 
     * This is the ONLY method that can grant final approval.
     * Protected by Gate and Policy to ensure only BAC can access.
     */
    public function approve(Request $request, PurchaseRequest $purchaseRequest)
    {
        // STRICT: Gate check for BAC role
        if (Gate::denies('bac-approve')) {
            abort(403, 'Only BAC members are authorized to approve purchase requests.');
        }

        // Policy check for this specific PR
        $this->authorize('approve', $purchaseRequest);

        $validated = $request->validate([
            'remarks' => 'nullable|string|max:1000',
            'fund_cluster' => 'nullable|string|max:50',
            'funds_available' => 'nullable|numeric|min:0',
        ]);

        $currentStatus = (int) $purchaseRequest->status_id;

        // Validate current status allows approval
        if (!in_array($currentStatus, [Status::PR_RECOMMENDED, Status::PR_FOR_APPROVAL], true)) {
            throw ValidationException::withMessages([
                'status' => 'This purchase request cannot be approved in its current state.',
            ]);
        }

        // Prevent self-approval
        if ($purchaseRequest->account_id === Auth::id()) {
            throw ValidationException::withMessages([
                'status' => 'You cannot approve a purchase request you submitted.',
            ]);
        }

        DB::transaction(function () use ($purchaseRequest, $validated, $currentStatus) {
            $accountId = Auth::id();
            $now = now();

            // Update PR to Approved status
            $purchaseRequest->update([
                'status_id' => Status::PR_APPROVED,
                'approved_by' => $accountId,
                'approved_at' => $now,
                'approval_remarks' => $validated['remarks'] ?? null,
                'fund_cluster' => $validated['fund_cluster'] ?? $purchaseRequest->fund_cluster,
                'funds_available' => $validated['funds_available'] ?? $purchaseRequest->funds_available,
            ]);

            // Record status history
            StatusHistory::create([
                'table_name' => 'purchase_requests',
                'record_id' => $purchaseRequest->pr_no,
                'old_status_id' => $currentStatus,
                'new_status_id' => Status::PR_APPROVED,
                'changed_by' => $accountId,
                'remarks' => $validated['remarks'] ?? 'BAC final approval granted.',
                'changed_at' => $now,
            ]);

            // Create audit log
            AuditLog::create([
                'account_id' => $accountId,
                'table_name' => 'purchase_requests',
                'action' => 'BAC_APPROVAL',
                'description' => "BAC approved purchase request {$purchaseRequest->pr_no}",
                'log_time' => $now,
            ]);

            // Notify the requester
            Notification::create([
                'recipient_id' => $purchaseRequest->account_id,
                'sender_id' => $accountId,
                'table_name' => 'purchase_requests',
                'record_id' => $purchaseRequest->pr_no,
                'message' => "Your purchase request {$purchaseRequest->pr_no} has received BAC final approval.",
                'type' => 'success',
            ]);

            // Notify custodians to proceed with PO generation
            $custodians = Account::where('role', 'custodian')->pluck('account_id');
            foreach ($custodians as $custodianId) {
                Notification::create([
                    'recipient_id' => $custodianId,
                    'sender_id' => $accountId,
                    'table_name' => 'purchase_requests',
                    'record_id' => $purchaseRequest->pr_no,
                    'message' => "Purchase request {$purchaseRequest->pr_no} approved by BAC. Ready for purchase order generation.",
                    'type' => 'task',
                ]);
            }
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Purchase request approved successfully.',
                'data' => $this->transformPurchaseRequest($purchaseRequest->fresh()),
            ]);
        }

        return redirect()
            ->route('bac.requests.show', $purchaseRequest)
            ->with('success', 'Purchase request approved successfully.');
    }

    /**
     * Move PR to "For BAC Approval" status for detailed review.
     */
    public function moveToReview(Request $request, PurchaseRequest $purchaseRequest)
    {
        if (Gate::denies('bac-approve')) {
            abort(403, 'Only BAC members can access this action.');
        }

        $currentStatus = (int) $purchaseRequest->status_id;

        if ($currentStatus !== Status::PR_RECOMMENDED) {
            throw ValidationException::withMessages([
                'status' => 'Only recommended purchase requests can be moved to BAC review.',
            ]);
        }

        DB::transaction(function () use ($purchaseRequest, $currentStatus) {
            $accountId = Auth::id();
            $now = now();

            $purchaseRequest->update([
                'status_id' => Status::PR_FOR_APPROVAL,
            ]);

            StatusHistory::create([
                'table_name' => 'purchase_requests',
                'record_id' => $purchaseRequest->pr_no,
                'old_status_id' => $currentStatus,
                'new_status_id' => Status::PR_FOR_APPROVAL,
                'changed_by' => $accountId,
                'remarks' => 'Moved to BAC review queue.',
                'changed_at' => $now,
            ]);

            AuditLog::create([
                'account_id' => $accountId,
                'table_name' => 'purchase_requests',
                'action' => 'STATUS_UPDATE',
                'description' => "Moved purchase request {$purchaseRequest->pr_no} to BAC review",
                'log_time' => $now,
            ]);

            // Notify requester
            Notification::create([
                'recipient_id' => $purchaseRequest->account_id,
                'sender_id' => $accountId,
                'table_name' => 'purchase_requests',
                'record_id' => $purchaseRequest->pr_no,
                'message' => "Your purchase request {$purchaseRequest->pr_no} is now under BAC review.",
                'type' => 'info',
            ]);
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Purchase request moved to BAC review.',
            ]);
        }

        return back()->with('success', 'Purchase request moved to BAC review.');
    }

    /**
     * Cancel/Reject a purchase request.
     */
    public function cancel(Request $request, PurchaseRequest $purchaseRequest)
    {
        if (Gate::denies('bac-approve')) {
            abort(403, 'Only BAC members can access this action.');
        }

        $this->authorize('cancel', $purchaseRequest);

        $validated = $request->validate([
            'remarks' => 'required|string|max:1000',
        ]);

        $currentStatus = (int) $purchaseRequest->status_id;

        if (!in_array($currentStatus, [Status::PR_RECOMMENDED, Status::PR_FOR_APPROVAL], true)) {
            throw ValidationException::withMessages([
                'status' => 'This purchase request cannot be cancelled in its current state.',
            ]);
        }

        DB::transaction(function () use ($purchaseRequest, $validated, $currentStatus) {
            $accountId = Auth::id();
            $now = now();

            // Release reserved funds back to allocation
            if ($purchaseRequest->fundAllocation && $purchaseRequest->total_estimated_cost > 0) {
                $fundService = new \App\Services\FundAllocationService();
                $fundService->release(
                    $purchaseRequest->fundAllocation, 
                    $purchaseRequest->total_estimated_cost, 
                    $purchaseRequest->pr_no
                );
            }

            $purchaseRequest->update([
                'status_id' => Status::PR_CANCELLED,
                'approval_remarks' => $validated['remarks'],
            ]);

            StatusHistory::create([
                'table_name' => 'purchase_requests',
                'record_id' => $purchaseRequest->pr_no,
                'old_status_id' => $currentStatus,
                'new_status_id' => Status::PR_CANCELLED,
                'changed_by' => $accountId,
                'remarks' => $validated['remarks'],
                'changed_at' => $now,
            ]);

            AuditLog::create([
                'account_id' => $accountId,
                'table_name' => 'purchase_requests',
                'action' => 'BAC_REJECTION',
                'description' => "BAC cancelled purchase request {$purchaseRequest->pr_no}. Reason: {$validated['remarks']}",
                'log_time' => $now,
            ]);

            // Notify requester
            Notification::create([
                'recipient_id' => $purchaseRequest->account_id,
                'sender_id' => $accountId,
                'table_name' => 'purchase_requests',
                'record_id' => $purchaseRequest->pr_no,
                'message' => "Your purchase request {$purchaseRequest->pr_no} was cancelled by BAC.\nReason: {$validated['remarks']}",
                'type' => 'warning',
            ]);
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Purchase request cancelled.',
            ]);
        }

        return redirect()
            ->route('bac.requests.index')
            ->with('success', 'Purchase request cancelled.');
    }

    /**
     * Transform purchase request for JSON response.
     */
    protected function transformPurchaseRequest(PurchaseRequest $pr): array
    {
        return [
            'pr_no' => $pr->pr_no,
            'status' => $pr->status?->status_name,
            'status_id' => $pr->status_id,
            'purpose' => $pr->purpose,
            'sai_no' => $pr->sai_no,
            'alobs_no' => $pr->alobs_no,
            'fund_cluster' => $pr->fund_cluster,
            'funds_available' => $pr->funds_available,
            'total_estimated_cost' => $pr->total_estimated_cost,
            'division' => $pr->division?->division_name,
            'section' => $pr->section?->section_name,
            'requester' => $pr->requester?->employee
                ? collect([
                    $pr->requester->employee->first_name,
                    $pr->requester->employee->middle_name,
                    $pr->requester->employee->last_name,
                    $pr->requester->employee->suffix,
                ])->filter()->implode(' ')
                : 'Unknown',
            'recommended_by_name' => $pr->recommender?->employee
                ? collect([
                    $pr->recommender->employee->first_name,
                    $pr->recommender->employee->middle_name,
                    $pr->recommender->employee->last_name,
                    $pr->recommender->employee->suffix,
                ])->filter()->implode(' ')
                : null,
            'recommended_at' => $pr->recommended_at instanceof \DateTimeInterface
                ? $pr->recommended_at->toDateTimeString()
                : ($pr->recommended_at ? (string) $pr->recommended_at : null),
            'recommendation_remarks' => $pr->recommendation_remarks,
            'approved_by' => $pr->approver?->employee
                ? collect([
                    $pr->approver->employee->first_name,
                    $pr->approver->employee->middle_name,
                    $pr->approver->employee->last_name,
                    $pr->approver->employee->suffix,
                ])->filter()->implode(' ')
                : null,
            'approved_at' => $pr->approved_at instanceof \DateTimeInterface
                ? $pr->approved_at->toDateTimeString()
                : ($pr->approved_at ? (string) $pr->approved_at : null),
            'approval_remarks' => $pr->approval_remarks,
            'created_at' => $pr->created_at instanceof \DateTimeInterface
                ? $pr->created_at->toDateTimeString()
                : ($pr->created_at ? (string) $pr->created_at : null),
            'items' => $pr->items->map(fn ($item) => [
                'pri_id' => $item->pri_id,
                'item_description' => $item->item_description,
                'quantity' => $item->quantity,
                'unit' => $item->unit,
                'stock_property_no' => $item->stock_property_no,
                'estimated_unit_cost' => $item->estimated_unit_cost,
                'estimated_total_cost' => $item->estimated_total_cost,
            ])->toArray(),
        ];
    }
}
