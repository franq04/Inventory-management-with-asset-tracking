<?php

namespace App\Http\Controllers\DivisionHead;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\PurchaseRequest;
use App\Models\Status;
use App\Models\StatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

/**
 * Division Head Controller for Purchase Request Recommendations
 * 
 * Workflow:
 * 1. Employee submits PR with status "For Recommendation" (102)
 * 2. Division Head reviews and either:
 *    - Recommends (103) → Goes to BAC for final approval
 *    - Cancels (106) → Request rejected with remarks
 */
class PurchaseRequestController extends Controller
{
    /**
     * Display purchase requests for Division Head review.
     */
    public function index(Request $request)
    {
        if (Gate::denies('division-head-recommend')) {
            abort(403, 'Only Division Heads can access this page.');
        }

        $tabs = [
            'for_recommendation' => [
                'label' => 'Pending Recommendation',
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
        ];

        $activeTab = $request->query('tab', 'for_recommendation');
        if (!array_key_exists($activeTab, $tabs)) {
            $activeTab = 'for_recommendation';
        }

        $query = PurchaseRequest::with(['status', 'requester.employee', 'division', 'section'])
            ->orderByDesc('created_at');

        $selectedStatuses = $tabs[$activeTab]['statuses'];
        $query->whereIn('status_id', $selectedStatuses);

        $purchaseRequests = $query->paginate(10)->withQueryString();

        $tabCounts = [];
        foreach ($tabs as $key => $definition) {
            $tabCounts[$key] = PurchaseRequest::whereIn('status_id', $definition['statuses'])->count();
        }

        $statuses = Status::whereIn('status_id', [
            Status::PR_FOR_RECOMMENDATION,
            Status::PR_RECOMMENDED,
            Status::PR_CANCELLED,
        ])->get();

        return view('division_head.purchase_requests.index', [
            'purchaseRequests' => $purchaseRequests,
            'statuses' => $statuses,
            'activeTab' => $activeTab,
            'tabCounts' => $tabCounts,
            'tabs' => collect($tabs)->mapWithKeys(fn ($def, $key) => [$key => $def['label']])->toArray(),
        ]);
    }

    /**
     * Display a single purchase request for Division Head review.
     */
    public function show(Request $request, PurchaseRequest $purchaseRequest)
    {
        if (Gate::denies('division-head-recommend')) {
            abort(403, 'Only Division Heads can access this page.');
        }

        $purchaseRequest->load([
            'items',
            'status',
            'requester.employee',
            'division',
            'section',
            'statusHistory' => fn ($q) => $q->orderByDesc('changed_at'),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'data' => $this->transformPurchaseRequest($purchaseRequest),
            ]);
        }

        return view('division_head.purchase_requests.show', compact('purchaseRequest'));
    }

    /**
     * Division Head Recommendation Action
     * 
     * Moves PR from "For Recommendation" (102) to "Recommended" (103).
     * After this, the PR goes to BAC for final approval.
     */
    public function recommend(Request $request, PurchaseRequest $purchaseRequest)
    {
        if (Gate::denies('division-head-recommend')) {
            abort(403, 'Only Division Heads can provide recommendations.');
        }

        $this->authorize('recommend', $purchaseRequest);

        $validated = $request->validate([
            'remarks' => 'nullable|string|max:1000',
        ]);

        $currentStatus = (int) $purchaseRequest->status_id;

        if ($currentStatus !== Status::PR_FOR_RECOMMENDATION) {
            throw ValidationException::withMessages([
                'status' => 'This purchase request is not awaiting recommendation.',
            ]);
        }

        DB::transaction(function () use ($purchaseRequest, $validated, $currentStatus) {
            $accountId = Auth::id();
            $now = now();

            // Update PR to Recommended status
            $purchaseRequest->update([
                'status_id' => Status::PR_RECOMMENDED,
                'recommended_by' => $accountId,
                'recommended_at' => $now,
                'recommendation_remarks' => $validated['remarks'] ?? null,
            ]);

            // Record status history
            StatusHistory::create([
                'table_name' => 'purchase_requests',
                'record_id' => $purchaseRequest->pr_no,
                'old_status_id' => $currentStatus,
                'new_status_id' => Status::PR_RECOMMENDED,
                'changed_by' => $accountId,
                'remarks' => $validated['remarks'] ?? 'Division Head recommendation provided.',
                'changed_at' => $now,
            ]);

            // Create audit log
            AuditLog::create([
                'account_id' => $accountId,
                'table_name' => 'purchase_requests',
                'action' => 'RECOMMENDATION',
                'description' => "Division Head recommended purchase request {$purchaseRequest->pr_no}",
                'log_time' => $now,
            ]);

            // Notify the requester
            Notification::create([
                'recipient_id' => $purchaseRequest->account_id,
                'sender_id' => $accountId,
                'table_name' => 'purchase_requests',
                'record_id' => $purchaseRequest->pr_no,
                'message' => "Your purchase request {$purchaseRequest->pr_no} has been recommended by the Division Head and forwarded to BAC for final approval.",
                'type' => 'info',
            ]);

            // Notify BAC members
            $bacMembers = Account::where('role', 'bac')->pluck('account_id');
            foreach ($bacMembers as $bacId) {
                Notification::create([
                    'recipient_id' => $bacId,
                    'sender_id' => $accountId,
                    'table_name' => 'purchase_requests',
                    'record_id' => $purchaseRequest->pr_no,
                    'message' => "Purchase request {$purchaseRequest->pr_no} has been recommended by Division Head and requires BAC approval.",
                    'type' => 'task',
                ]);
            }
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Purchase request recommended successfully.',
                'data' => $this->transformPurchaseRequest($purchaseRequest->fresh()),
            ]);
        }

        return redirect()
            ->route('division.requests.show', $purchaseRequest)
            ->with('success', 'Purchase request recommended and forwarded to BAC.');
    }

    /**
     * Cancel/Reject a purchase request at recommendation stage.
     */
    public function cancel(Request $request, PurchaseRequest $purchaseRequest)
    {
        if (Gate::denies('division-head-recommend')) {
            abort(403, 'Only Division Heads can access this action.');
        }

        $this->authorize('cancel', $purchaseRequest);

        $validated = $request->validate([
            'remarks' => 'required|string|max:1000',
        ]);

        $currentStatus = (int) $purchaseRequest->status_id;

        if ($currentStatus !== Status::PR_FOR_RECOMMENDATION) {
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
                'action' => 'RECOMMENDATION_REJECTED',
                'description' => "Division Head cancelled purchase request {$purchaseRequest->pr_no}. Reason: {$validated['remarks']}",
                'log_time' => $now,
            ]);

            Notification::create([
                'recipient_id' => $purchaseRequest->account_id,
                'sender_id' => $accountId,
                'table_name' => 'purchase_requests',
                'record_id' => $purchaseRequest->pr_no,
                'message' => "Your purchase request {$purchaseRequest->pr_no} was not recommended by the Division Head.\nReason: {$validated['remarks']}",
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
            ->route('division.requests.index')
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
                ])->filter()->implode(' ')
                : 'Unknown',
            'recommended_at' => $pr->recommended_at?->toDateTimeString(),
            'recommendation_remarks' => $pr->recommendation_remarks,
            'created_at' => $pr->created_at?->toDateTimeString(),
            'items' => $pr->items->map(fn ($item) => [
                'pri_id' => $item->pri_id,
                'description' => $item->item_description,
                'quantity' => $item->quantity,
                'unit' => $item->unit,
                'unit_cost' => $item->estimated_unit_cost,
                'total_cost' => $item->estimated_total_cost,
            ])->toArray(),
        ];
    }
}
