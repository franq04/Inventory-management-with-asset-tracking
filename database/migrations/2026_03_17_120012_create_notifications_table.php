<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('notifications')) {
            return;
        }

        DB::statement('CREATE TABLE `notifications` (
  `notification_id` bigint(20) UNSIGNED NOT NULL,
  `recipient_id` bigint(20) UNSIGNED NOT NULL,
  `sender_id` bigint(20) UNSIGNED DEFAULT NULL,
  `table_name` enum(\'purchase_requests\',\'purchase_orders\',\'inspection_acceptance\') DEFAULT NULL,
  `record_id` varchar(100) DEFAULT NULL,
  `message` text NOT NULL,
  `type` enum(\'info\',\'success\',\'warning\',\'error\',\'task\',\'action_required\') NOT NULL DEFAULT \'info\',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;');
        DB::statement('ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `recipient_id` (`recipient_id`),
  ADD KEY `sender_id` (`sender_id`);');
          DB::statement('ALTER TABLE `notifications`
      ADD KEY `idx_notif_recipient_read_date` (`recipient_id`,`is_read`,`created_at`);');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
