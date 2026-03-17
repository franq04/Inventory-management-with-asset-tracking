<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AuditLog;
use App\Models\FundAllocation;
use App\Models\Notification;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\Section;
use App\Models\Status;
use App\Models\StatusHistory;
use App\Services\FundAllocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PurchaseRequestController extends Controller
{
    public function index()
    {
        $account = Auth::user();

        $baseQuery = PurchaseRequest::query()
            ->where('account_id', $account->account_id)
            ->orderByDesc('created_at');

        $purchaseRequests = (clone $baseQuery)
            ->with(['status'])
            ->paginate(5)
            ->withQueryString();

        $statusCounts = (clone $baseQuery)
            ->select('status_id', DB::raw('COUNT(*) as total'))
            ->groupBy('status_id')
            ->get();

        $statusNames = Status::whereIn('status_id', $statusCounts->pluck('status_id'))
            ->pluck('status_name', 'status_id');

        $statusSummary = $statusCounts
            ->map(function ($row) use ($statusNames) {
                $statusId = (int) $row->status_id;
                $name = $statusNames[$statusId] ?? 'Unknown';
                $slug = Str::slug($name, '_') ?: 'unknown';

                return [
                    'slug' => $slug,
                    'name' => $name,
                    'count' => (int) $row->total,
                ];
            })
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->mapWithKeys(function ($item) {
                return [$item['slug'] => [
                    'name' => $item['name'],
                    'count' => $item['count'],
                ]];
            });

        $totalRequests = (int) $purchaseRequests->total();

        $accountProfile = Account::query()
            ->with('employee.section.division')
            ->find($account->account_id);

        $employeeSection = $accountProfile?->employee?->section;
        $employeeDivision = $employeeSection?->division;

        $defaultDivision = $employeeDivision?->division_id;
        $defaultSection = $employeeSection?->section_id;
        $divisionName = $employeeDivision?->division_name;
        $sectionName = $employeeSection?->section_name;

        // Get available fund allocations
        $fundAllocations = FundAllocation::where('remaining_amount', '>', 0)
            ->orderBy('fund_cluster')
            ->get(['id', 'fund_cluster', 'total_amount', 'remaining_amount']);

        return view('employee.purchase_requests.index', [
            'purchaseRequests' => $purchaseRequests,
            'statusSummary' => $statusSummary,
            'totalRequests' => $totalRequests,
            'defaultDivision' => $defaultDivision,
            'defaultSection' => $defaultSection,
            'divisionName' => $divisionName,
            'sectionName' => $sectionName,
            'fundAllocations' => $fundAllocations,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'division_id' => 'required|exists:divisions,division_id',
            'section_id' => 'required|exists:sections,section_id',
            'purpose' => 'required|string|max:5000',
            'sai_no' => 'nullable|string|max:100',
            'alobs_no' => 'nullable|string|max:100',
            'recommending_officer_id' => 'nullable|string|max:255',
            'fund_allocation_id' => 'required|exists:fund_allocations,id',
            'items' => 'required|array|min:1',
            'items.*.item_description' => 'required|string|max:500',
            'items.*.stock_number' => 'nullable|string|max:100',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit' => 'required|string|max:100',
            'items.*.estimated_unit_cost' => 'required|numeric|min:0',
        ]);

        $section = Section::where('section_id', $validated['section_id'])
            ->where('division_id', $validated['division_id'])
            ->first();

        if (! $section) {
            return response()->json([
                'status' => 'error',
                'message' => 'Selected section does not belong to the chosen division.',
            ], 422);
        }

        $account = Auth::user();

        // Calculate total cost before transaction
        $items = collect($validated['items']);
        $totalEstimated = $items->sum(function ($item) {
            return (int) $item['quantity'] * (float) $item['estimated_unit_cost'];
        });

        // Check fund allocation availability
        $fundAllocation = FundAllocation::findOrFail($validated['fund_allocation_id']);
        $fundService = new FundAllocationService();
        
        if (!$fundService->hasSufficientFunds($fundAllocation, $totalEstimated)) {
            $shortfall = $fundService->getShortfall($fundAllocation, $totalEstimated);
            return response()->json([
                'status' => 'error',
                'errors' => [
                    'fund_allocation_id' => [
                        sprintf(
                            'Selected fund cluster does not have sufficient balance. Available: ₱%s, Required: ₱%s, Shortfall: ₱%s',
                            number_format($fundAllocation->remaining_amount, 2),
                            number_format($totalEstimated, 2),
                            number_format($shortfall, 2)
                        )
                    ]
                ],
                'message' => 'Insufficient funds available.',
            ], 422);
        }

        $purchaseRequest = DB::transaction(function () use ($validated, $account, $items, $totalEstimated, $fundAllocation, $fundService) {
            $prNumber = $this->generatePrNumber();
            
            // Atomically reserve funds
            if (!$fundService->reserve($fundAllocation, $totalEstimated, $prNumber)) {
                throw ValidationException::withMessages([
                    'fund_allocation_id' => ['Failed to reserve funds. The fund allocation may have been modified by another transaction.']
                ]);
            }

            $purchaseRequest = PurchaseRequest::create([
                'pr_no' => $prNumber,
                'account_id' => $account->account_id,
                'status_id' => Status::PR_FOR_RECOMMENDATION,
                'division_id' => $validated['division_id'],
                'section_id' => $validated['section_id'],
                'sai_no' => $validated['sai_no'] ?? null,
                'alobs_no' => $validated['alobs_no'] ?? null,
                'purpose' => $validated['purpose'],
                'recommending_officer_id' => $validated['recommending_officer_id'] ?? null,
                'fund_allocation_id' => $validated['fund_allocation_id'],
                'funds_available' => $fundAllocation->fresh()->remaining_amount,
                'total_estimated_cost' => $totalEstimated,
                'printed' => false,
            ]);

            $items->each(function ($item) use ($purchaseRequest) {
                PurchaseRequestItem::create([
                    'pr_no' => $purchaseRequest->pr_no,
                    'item_description' => $item['item_description'],
                    'item_type' => $item['item_type'] ?? 'consumable',
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'stock_number' => $item['stock_number'] ?? null,
                    'estimated_unit_cost' => $item['estimated_unit_cost'],
                    'remarks' => $item['remarks'] ?? null,
                ]);
            });

            StatusHistory::create([
                'table_name' => 'purchase_requests',
                'record_id' => $purchaseRequest->pr_no,
                'old_status_id' => null,
                'new_status_id' => Status::PR_FOR_RECOMMENDATION,
                'changed_by' => $account->account_id,
                'remarks' => 'Initial submission',
            ]);

            AuditLog::create([
                'account_id' => $account->account_id,
                'table_name' => 'purchase_requests',
                'action' => 'CREATE',
                'description' => sprintf('Created purchase request %s', $purchaseRequest->pr_no),
            ]);

            return $purchaseRequest;
        });

        $this->notifyDivisionHeadsOfNewRequest($purchaseRequest, $account);

        return response()->json([
            'status' => 'success',
            'message' => 'Purchase request submitted successfully.',
            'data' => [
                'pr_no' => $purchaseRequest->pr_no,
            ],
        ]);
    }

    public function show(Request $request, PurchaseRequest $purchaseRequest)
    {
        $account = Auth::user();
        abort_unless($purchaseRequest->account_id === $account->account_id, 403);

        $purchaseRequest->load(['items', 'status', 'division', 'section', 'statusHistory' => function ($query) {
            $query->orderByDesc('changed_at')->limit(1);
        }]);

        $latestHistory = $purchaseRequest->statusHistory->first();

        if ($request->wantsJson()) {
            return response()->json([
                'data' => [
                    'pr_no' => $purchaseRequest->pr_no,
                    'status' => $purchaseRequest->status?->status_name,
                    'status_id' => $purchaseRequest->status_id,
                    'latest_status_remarks' => $latestHistory?->remarks,
                    'latest_status_changed_at' => optional($latestHistory?->changed_at)->toDateTimeString(),
                    'purpose' => $purchaseRequest->purpose,
                    'sai_no' => $purchaseRequest->sai_no,
                    'alobs_no' => $purchaseRequest->alobs_no,
                    'division' => $purchaseRequest->division?->division_name,
                    'section' => $purchaseRequest->section?->section_name,
                    'total_estimated_cost' => $purchaseRequest->total_estimated_cost,
                    'items' => $purchaseRequest->items->map(fn (PurchaseRequestItem $item) => $this->transformItem($item)),
                ],
            ]);
        }

        // Render HTML view for normal browser requests
        return view('employee.purchase_requests.show', [
            'purchaseRequest' => $purchaseRequest,
            'latestHistory' => $latestHistory,
        ]);
    }

    public function respondToItem(Request $request, PurchaseRequestItem $purchaseRequestItem)
    {
        $account = Auth::user();
        $purchaseRequestItem->loadMissing('purchaseRequest');
        $purchaseRequest = $purchaseRequestItem->purchaseRequest;

        abort_unless($purchaseRequest && $purchaseRequest->account_id === $account->account_id, 403);

        // Allow response when PR is in Recommended or For Approval status
        $allowedStatuses = [Status::PR_RECOMMENDED, Status::PR_FOR_APPROVAL];
        if (! in_array((int) $purchaseRequest->status_id, $allowedStatuses, true)) {
            return response()->json([
                'status' => 'error',
                'message' => 'This purchase request is not currently awaiting your decision.',
            ], 422);
        }

        $validated = $request->validate([
            'decision' => 'required|string|in:accept,wait',
            'wait_until' => 'required_if:decision,wait|date|after_or_equal:today',
            'wait_note' => 'nullable|string|max:255',
        ]);

        $waitNote = isset($validated['wait_note']) ? trim((string) $validated['wait_note']) : null;

        // Check if item has a pending alternative
        if (! $purchaseRequestItem->hasPendingAlternative() && $purchaseRequestItem->fulfillment_status !== 'unavailable') {
            return response()->json([
                'status' => 'error',
                'message' => 'This item does not require a decision right now.',
            ], 422);
        }

        if ($purchaseRequestItem->fulfillment_status === 'unavailable' && $validated['decision'] !== 'wait') {
            return response()->json([
                'status' => 'error',
                'message' => 'Unavailable items can only record a wait timeframe.',
            ], 422);
        }

        $waitUntil = null;

        if ($validated['decision'] === 'wait') {
            try {
                $waitUntil = Carbon::parse($validated['wait_until'])->startOfDay();
            } catch (\Exception $exception) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Please provide a valid date for your wait timeframe.',
                ], 422);
            }
        }

        $now = now();
        $originalDescription = $purchaseRequestItem->original_description ?: $purchaseRequestItem->item_description;
        $alternateDescription = $purchaseRequestItem->alternate_description;

        if ($validated['decision'] === 'accept') {
            // Accept the alternative: replace item description with alternate
            $purchaseRequestItem->employee_decision = 'accept';
            $purchaseRequestItem->employee_wait_until = null;
            $purchaseRequestItem->employee_wait_note = null;
            
            // Update item description to the alternative
            if ($alternateDescription) {
                $purchaseRequestItem->item_description = $alternateDescription;
            }
            $purchaseRequestItem->fulfillment_status = 'fulfilled';
        } else {
            // Wait for original: keep original item, set wait date
            $purchaseRequestItem->employee_decision = 'wait';
            $purchaseRequestItem->employee_wait_until = $waitUntil;
            $purchaseRequestItem->employee_wait_note = $waitNote ?: null;
            
            // Restore original description if it was changed
            if ($purchaseRequestItem->original_description) {
                $purchaseRequestItem->item_description = $purchaseRequestItem->original_description;
            }
            // Mark as waiting - original item retained
            $purchaseRequestItem->fulfillment_status = 'waiting';
        }

        $purchaseRequestItem->employee_decided_at = $now;
        $purchaseRequestItem->save();

        // Notify BAC members about the decision
        $bacMembers = Account::where('role', 'bac')->pluck('account_id');

        $itemSummary = Str::limit($originalDescription, 60);

        if ($validated['decision'] === 'accept') {
            $decisionMessage = sprintf(
                'accepted the alternative item "%s"',
                Str::limit($alternateDescription, 50)
            );
        } else {
            $decisionMessage = sprintf(
                'chose to wait for the original item "%s" until %s',
                $itemSummary,
                optional($waitUntil)->format('M d, Y')
            );
        }

        $message = sprintf(
            '%s has %s for PR %s.',
            $account->username,
            $decisionMessage,
            $purchaseRequestItem->pr_no
        );

        if ($waitNote) {
            $message .= ' Note: ' . $waitNote;
        }

        // Notify BAC
        $bacMembers->unique()->each(function ($recipientId) use ($account, $purchaseRequestItem, $message, $validated) {
            Notification::create([
                'recipient_id' => $recipientId,
                'sender_id' => $account->account_id,
                'table_name' => 'purchase_requests',
                'record_id' => $purchaseRequestItem->pr_no,
                'message' => $message,
                'type' => $validated['decision'] === 'wait' ? 'warning' : 'success',
                'is_read' => false,
                'created_at' => now(),
            ]);
        });

        // Also notify custodians
        $custodians = Account::where('role', 'custodian')->pluck('account_id');
        $custodians->unique()->each(function ($recipientId) use ($account, $purchaseRequestItem, $message, $validated) {
            Notification::create([
                'recipient_id' => $recipientId,
                'sender_id' => $account->account_id,
                'table_name' => 'purchase_requests',
                'record_id' => $purchaseRequestItem->pr_no,
                'message' => $message,
                'type' => $validated['decision'] === 'wait' ? 'warning' : 'info',
                'is_read' => false,
                'created_at' => now(),
            ]);
        });

        // Log the action
        AuditLog::create([
            'account_id' => $account->account_id,
            'table_name' => 'purchase_request_items',
            'action' => $validated['decision'] === 'accept' ? 'ACCEPT_ALTERNATIVE' : 'WAIT_FOR_ORIGINAL',
            'description' => $message,
            'log_time' => $now,
        ]);

        $responseMessage = $validated['decision'] === 'accept'
            ? 'Thank you! The purchase request will proceed with the alternative item.'
            : 'Thanks for the update. We will monitor the item availability until your specified date.';

        return response()->json([
            'status' => 'success',
            'message' => $responseMessage,
            'data' => [
                'item' => $this->transformItem($purchaseRequestItem->fresh()),
                'request_status_id' => $purchaseRequestItem->purchaseRequest->status_id,
            ],
        ]);
    }

    protected function transformItem(PurchaseRequestItem $item): array
    {
        return [
            'pri_id' => $item->pri_id,
            'item_description' => $item->item_description,
            'original_description' => $item->original_description,
            'item_type' => $item->item_type,
            'quantity' => $item->quantity,
            'unit' => $item->unit,
            'stock_number' => $item->stock_number,
            'estimated_unit_cost' => $item->estimated_unit_cost,
            'estimated_total_cost' => $item->estimated_total_cost,
            'remarks' => $item->remarks,
            'fulfillment_status' => $item->fulfillment_status,
            'alternate_description' => $item->alternate_description,
            'suggested_by' => $item->suggested_by,
            'suggested_at' => optional($item->suggested_at)->toDateTimeString(),
            'employee_decision' => $item->employee_decision,
            'employee_decided_at' => optional($item->employee_decided_at)->toDateTimeString(),
            'employee_wait_until' => optional($item->employee_wait_until)->toDateString(),
            'employee_wait_note' => $item->employee_wait_note,
            'removed_at' => optional($item->removed_at)->toDateTimeString(),
            'removal_reason' => $item->removal_reason,
            'has_pending_alternative' => $item->hasPendingAlternative(),
            'is_waiting_for_original' => $item->isWaitingForOriginal(),
            'is_wait_expired' => $item->isWaitExpired(),
            'is_active' => $item->isActive(),
        ];
    }

    protected function generatePrNumber(): string
    {
        $datePart = now()->format('Ymd');
        $prefix = 'PR-' . $datePart . '-';

        $lastPr = PurchaseRequest::where('pr_no', 'like', $prefix . '%')
            ->orderByDesc('pr_no')
            ->first();

        $nextSequence = 1;

        if ($lastPr) {
            $lastSequence = (int) Str::afterLast($lastPr->pr_no, '-');
            $nextSequence = $lastSequence + 1;
        }

        return $prefix . str_pad((string) $nextSequence, 3, '0', STR_PAD_LEFT);
    }

    protected function notifyDivisionHeadsOfNewRequest(PurchaseRequest $purchaseRequest, $account): void
    {
        $divisionHeadIds = Account::query()
            ->select('accounts.account_id')
            ->join('employees', 'employees.account_id', '=', 'accounts.account_id')
            ->join('sections', 'sections.section_id', '=', 'employees.section_id')
            ->where('accounts.role', 'division_head')
            ->where('sections.division_id', (int) $purchaseRequest->division_id)
            ->pluck('accounts.account_id')
            ->unique();

        $message = sprintf(
            'New purchase request %s submitted by %s.',
            $purchaseRequest->pr_no,
            $account->username
        );

        foreach ($divisionHeadIds as $recipientId) {
            Notification::create([
                'recipient_id' => $recipientId,
                'sender_id' => $account->account_id,
                'table_name' => 'purchase_requests',
                'record_id' => $purchaseRequest->pr_no,
                'message' => $message,
                'type' => 'task',
                'is_read' => false,
                'created_at' => now(),
            ]);
        }
    }
}
