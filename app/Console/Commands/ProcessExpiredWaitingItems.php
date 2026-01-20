<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\Status;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProcessExpiredWaitingItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pr:process-expired-items';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process purchase request items where the wait period has expired. Removes items and notifies requesters.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Processing expired waiting items...');

        // Find items that are waiting and have passed their wait_until date
        // Only process items that haven't been removed yet and are in non-approved PRs
        $expiredItems = PurchaseRequestItem::query()
            ->whereNull('removed_at')
            ->where('employee_decision', 'wait')
            ->whereNotNull('employee_wait_until')
            ->whereDate('employee_wait_until', '<', now()->startOfDay())
            ->whereHas('purchaseRequest', function ($query) {
                // Only process for PRs that are not yet approved
                $query->whereIn('status_id', [
                    Status::PR_FOR_RECOMMENDATION,
                    Status::PR_RECOMMENDED,
                    Status::PR_FOR_APPROVAL,
                ]);
            })
            ->with(['purchaseRequest.requester'])
            ->get();

        if ($expiredItems->isEmpty()) {
            $this->info('No expired items found.');
            return Command::SUCCESS;
        }

        $this->info("Found {$expiredItems->count()} expired item(s) to process.");

        $processedCount = 0;
        $now = now();

        foreach ($expiredItems as $item) {
            try {
                DB::transaction(function () use ($item, $now, &$processedCount) {
                    $purchaseRequest = $item->purchaseRequest;
                    $requester = $purchaseRequest->requester;

                    // Mark item as removed
                    $item->update([
                        'removed_at' => $now,
                        'removal_reason' => sprintf(
                            'Item removed - wait period expired on %s. Original item was not available.',
                            $item->employee_wait_until->format('M d, Y')
                        ),
                        'fulfillment_status' => 'expired',
                    ]);

                    // Update the PR total cost (subtract the removed item's cost)
                    $itemCost = (float) $item->quantity * (float) $item->estimated_unit_cost;
                    $newTotal = max(0, (float) $purchaseRequest->total_estimated_cost - $itemCost);
                    $purchaseRequest->update([
                        'total_estimated_cost' => $newTotal,
                    ]);

                    // Notify the requester
                    if ($requester) {
                        Notification::create([
                            'recipient_id' => $requester->account_id,
                            'sender_id' => null, // System notification
                            'table_name' => 'purchase_requests',
                            'record_id' => $purchaseRequest->pr_no,
                            'message' => sprintf(
                                'Item "%s" has been removed from your purchase request %s. The wait period expired on %s and the original item was not available. The PR total has been adjusted.',
                                \Illuminate\Support\Str::limit($item->original_description ?: $item->item_description, 60),
                                $purchaseRequest->pr_no,
                                $item->employee_wait_until->format('M d, Y')
                            ),
                            'type' => 'warning',
                            'is_read' => false,
                            'created_at' => $now,
                        ]);
                    }

                    // Notify BAC members
                    $bacMembers = Account::where('role', 'bac')->pluck('account_id');
                    foreach ($bacMembers as $bacId) {
                        Notification::create([
                            'recipient_id' => $bacId,
                            'sender_id' => null,
                            'table_name' => 'purchase_requests',
                            'record_id' => $purchaseRequest->pr_no,
                            'message' => sprintf(
                                'Item "%s" was automatically removed from PR %s. Wait period expired.',
                                \Illuminate\Support\Str::limit($item->original_description ?: $item->item_description, 50),
                                $purchaseRequest->pr_no
                            ),
                            'type' => 'info',
                            'is_read' => false,
                            'created_at' => $now,
                        ]);
                    }

                    // Create audit log
                    AuditLog::create([
                        'account_id' => null, // System action
                        'table_name' => 'purchase_request_items',
                        'action' => 'WAIT_EXPIRED',
                        'description' => sprintf(
                            'Item "%s" (pri_id: %d) removed from PR %s due to expired wait period.',
                            \Illuminate\Support\Str::limit($item->original_description ?: $item->item_description, 50),
                            $item->pri_id,
                            $purchaseRequest->pr_no
                        ),
                        'log_time' => $now,
                    ]);

                    $processedCount++;
                });

                $this->info("Processed: {$item->pr_no} - Item #{$item->pri_id}");

            } catch (\Exception $e) {
                $this->error("Failed to process item #{$item->pri_id}: {$e->getMessage()}");
                \Log::error("Failed to process expired item", [
                    'pri_id' => $item->pri_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Completed. Processed {$processedCount} item(s).");

        return Command::SUCCESS;
    }
}
