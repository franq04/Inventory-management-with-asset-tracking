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
        if (Schema::hasTable('fund_allocations')) {
            return;
        }

        DB::statement('CREATE TABLE `fund_allocations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `fund_cluster` varchar(255) NOT NULL,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `remaining_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
        DB::statement('ALTER TABLE `fund_allocations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fund_allocations_created_by_foreign` (`created_by`),
  ADD KEY `fund_allocations_fund_cluster_index` (`fund_cluster`);');
          DB::statement('ALTER TABLE `fund_allocations`
      ADD CONSTRAINT `fund_allocations_created_by_foreign`
      FOREIGN KEY (`created_by`) REFERENCES `accounts` (`account_id`) ON DELETE SET NULL;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fund_allocations');
    }
};
