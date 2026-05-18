<?php

namespace App\Console\Commands;

use App\Models\AssetMovement;
use App\Models\Employee;
use App\Models\Notification;
use App\Models\PqsRecord;
use Illuminate\Console\Command;

class NotifyMaintenanceDue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assets:notify-maintenance-due';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify custodians when maintenance expected completion dates are due or overdue.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = now()->startOfDay();
        $notified = 0;
        $skipped = 0;

        $latestMaintenance = AssetMovement::query()
            ->select('asset_movements.*')
            ->joinSub(
                AssetMovement::query()
                    ->selectRaw('property_no, MAX(movement_id) AS latest_id')
                    ->whereIn('movement_type', ['maintenance_out', 'maintenance_in'])
                    ->groupBy('property_no'),
                'latest_movements',
                function ($join): void {
                    $join->on('asset_movements.property_no', '=', 'latest_movements.property_no')
                        ->on('asset_movements.movement_id', '=', 'latest_movements.latest_id');
                }
            )
            ->where('asset_movements.movement_type', 'maintenance_out')
            ->whereNotNull('asset_movements.expected_return_at')
            ->whereDate('asset_movements.expected_return_at', '<=', $today)
            ->get();

        foreach ($latestMaintenance as $movement) {
            $pqs = PqsRecord::query()
                ->where('property_no', $movement->property_no)
                ->first(['asset_status', 'current_custodian_employee_id', 'accountable_officer_id']);

            if (! $pqs) {
                $skipped++;
                continue;
            }

            if (in_array($pqs->asset_status, [PqsRecord::STATUS_DISPOSED, PqsRecord::STATUS_LOST], true)) {
                continue;
            }

            if ($this->notificationExists((int) $movement->movement_id)) {
                continue;
            }

            $recipientId = $this->resolveRecipientId(
                (string) $movement->property_no,
                $pqs->current_custodian_employee_id,
                $pqs->accountable_officer_id
            );

            if (! $recipientId) {
                $skipped++;
                continue;
            }

            $expectedDate = optional($movement->expected_return_at)->format('M d, Y') ?: 'Unknown date';

            Notification::create([
                'recipient_id' => $recipientId,
                'sender_id' => null,
                'table_name' => 'asset_movements',
                'record_id' => (string) $movement->movement_id,
                'message' => sprintf(
                    'Maintenance due for asset %s. Expected completion was %s. Please mark as serviceable, unserviceable, or extend the maintenance date.',
                    $movement->property_no,
                    $expectedDate
                ),
                'type' => 'action_required',
                'is_read' => false,
                'created_at' => now(),
            ]);

            $notified++;
        }

        $this->info("Completed. Sent {$notified} notification(s). Skipped {$skipped}.");

        return Command::SUCCESS;
    }

    private function resolveRecipientId(string $propertyNo, ?string $custodianEmployeeId, ?string $accountableOfficerId): ?int
    {
        $recordedBy = AssetMovement::query()
            ->where('property_no', $propertyNo)
            ->where('movement_type', 'initial_assignment')
            ->whereNotNull('recorded_by')
            ->orderBy('effective_at')
            ->value('recorded_by');

        if ($recordedBy) {
            return (int) $recordedBy;
        }

        $employeeId = $custodianEmployeeId ?: $accountableOfficerId;
        if (! $employeeId) {
            return null;
        }

        $accountId = Employee::query()
            ->where('employee_id', $employeeId)
            ->value('account_id');

        return $accountId ? (int) $accountId : null;
    }

    private function notificationExists(int $movementId): bool
    {
        return Notification::query()
            ->where('table_name', 'asset_movements')
            ->where('record_id', (string) $movementId)
            ->where('type', 'action_required')
            ->exists();
    }
}
