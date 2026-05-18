<?php

namespace App\Console\Commands;

use App\Models\AssetMovement;
use App\Models\Employee;
use App\Models\IcsRecord;
use App\Models\Notification;
use App\Models\ParRecord;
use App\Models\PqsRecord;
use Carbon\Carbon;
use Illuminate\Console\Command;

class NotifyUnserviceableAssets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assets:notify-unserviceable';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify custodians when assets reach their estimated useful life.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = now()->startOfDay();
        $notified = 0;
        $skipped = 0;

        $icsQuery = IcsRecord::query()
            ->select([
                'ics.property_no',
                'ics.estimated_useful_life',
                'pqs.date_acquired',
                'pqs.asset_status',
                'pqs.current_custodian_employee_id',
                'pqs.accountable_officer_id',
            ])
            ->join('pqs', 'pqs.property_no', '=', 'ics.property_no')
            ->whereNotNull('ics.estimated_useful_life')
            ->where('ics.estimated_useful_life', '!=', '')
            ->orderBy('ics.property_no');

        $parQuery = ParRecord::query()
            ->select([
                'par.property_no',
                'par.estimated_useful_life',
                'pqs.date_acquired',
                'pqs.asset_status',
                'pqs.current_custodian_employee_id',
                'pqs.accountable_officer_id',
            ])
            ->join('pqs', 'pqs.property_no', '=', 'par.property_no')
            ->whereNotNull('par.estimated_useful_life')
            ->where('par.estimated_useful_life', '!=', '')
            ->orderBy('par.property_no');

        $this->processRecords($icsQuery, $today, $notified, $skipped);
        $this->processRecords($parQuery, $today, $notified, $skipped);

        $this->info("Completed. Sent {$notified} notification(s). Skipped {$skipped}.");

        return Command::SUCCESS;
    }

    private function processRecords($query, Carbon $today, int &$notified, int &$skipped): void
    {
        $query->chunk(200, function ($records) use (&$notified, &$skipped, $today): void {
            foreach ($records as $record) {
                if (! $record->date_acquired) {
                    $skipped++;
                    continue;
                }

                if (in_array($record->asset_status, [
                    PqsRecord::STATUS_DISPOSED,
                    PqsRecord::STATUS_LOST,
                    PqsRecord::STATUS_FOR_REPAIR,
                    PqsRecord::STATUS_MAINTENANCE,
                ], true)) {
                    continue;
                }

                $life = $this->parseUsefulLife((string) $record->estimated_useful_life);
                if (! $life) {
                    $skipped++;
                    continue;
                }

                $expiryDate = Carbon::parse($record->date_acquired)
                    ->startOfDay()
                    ->addMonthsNoOverflow($life['months']);

                if ($today->lt($expiryDate)) {
                    continue;
                }

                if ($this->notificationExists((string) $record->property_no)) {
                    continue;
                }

                $recipientId = $this->resolveRecipientId(
                    (string) $record->property_no,
                    $record->current_custodian_employee_id,
                    $record->accountable_officer_id
                );

                if (! $recipientId) {
                    $skipped++;
                    continue;
                }

                Notification::create([
                    'recipient_id' => $recipientId,
                    'sender_id' => null,
                    'table_name' => 'pqs',
                    'record_id' => (string) $record->property_no,
                    'message' => sprintf(
                        'Estimated useful life reached for asset %s on %s. Please assess and mark as unserviceable if needed.',
                        $record->property_no,
                        $expiryDate->format('M d, Y')
                    ),
                    'type' => 'warning',
                    'is_read' => false,
                    'created_at' => now(),
                ]);

                $notified++;
            }
        });
    }

    private function parseUsefulLife(string $raw): ?array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        if (! preg_match('/(\d+(?:\.\d+)?)/', $raw, $matches)) {
            return null;
        }

        $value = (float) $matches[1];
        if ($value <= 0) {
            return null;
        }

        $isMonths = (bool) preg_match('/\b(month|months|mo|mos|mth|mths)\b/i', $raw);
        $months = $isMonths ? (int) round($value) : (int) round($value * 12);
        $months = max(1, $months);

        return [
            'months' => $months,
        ];
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

    private function notificationExists(string $propertyNo): bool
    {
        return Notification::query()
            ->where('table_name', 'pqs')
            ->where('record_id', $propertyNo)
            ->where('type', 'warning')
            ->where('message', 'like', 'Estimated useful life reached%')
            ->exists();
    }
}
