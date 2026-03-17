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
        if (Schema::hasTable('status_history')) {
            return;
        }

        DB::statement('CREATE TABLE `status_history` (
  `history_id` bigint(20) UNSIGNED NOT NULL,
  `table_name` varchar(64) NOT NULL,
  `record_id` varchar(100) NOT NULL,
  `old_status_id` int(11) DEFAULT NULL,
  `new_status_id` int(11) NOT NULL,
  `changed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;');
        DB::statement('ALTER TABLE `status_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `fk_history_changed_by` (`changed_by`);');
          DB::statement('ALTER TABLE `status_history`
      ADD KEY `idx_sh_table_record_date` (`table_name`,`record_id`,`changed_at`);');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status_history');
    }
};
