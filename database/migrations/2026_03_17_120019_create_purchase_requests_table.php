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
        if (Schema::hasTable('purchase_requests')) {
            return;
        }

        DB::statement('CREATE TABLE `purchase_requests` (
  `pr_no` varchar(50) NOT NULL,
  `account_id` bigint(20) UNSIGNED NOT NULL,
  `status_id` int(11) NOT NULL,
  `reviewed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `recommended_by` bigint(20) UNSIGNED DEFAULT NULL,
  `recommended_at` timestamp NULL DEFAULT NULL,
  `recommendation_remarks` text DEFAULT NULL,
  `division_id` int(11) DEFAULT NULL,
  `section_id` int(11) DEFAULT NULL,
  `sai_no` varchar(100) DEFAULT NULL,
  `alobs_no` varchar(100) DEFAULT NULL,
  `fund_cluster` varchar(50) DEFAULT NULL,
  `funds_available` decimal(15,2) DEFAULT NULL,
  `purpose` text DEFAULT NULL,
  `recommending_officer_id` varchar(255) DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approval_remarks` text DEFAULT NULL,
  `total_estimated_cost` decimal(15,2) DEFAULT NULL,
  `fund_allocation_id` bigint(20) UNSIGNED DEFAULT NULL,
  `printed` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;');
        DB::statement('ALTER TABLE `purchase_requests`
  ADD PRIMARY KEY (`pr_no`),
  ADD KEY `fk_pr_account` (`account_id`),
  ADD KEY `fk_pr_status` (`status_id`),
  ADD KEY `fk_pr_reviewed_by` (`reviewed_by`),
  ADD KEY `fk_pr_division` (`division_id`),
  ADD KEY `fk_pr_section` (`section_id`),
  ADD KEY `fk_pr_recommending_officer` (`recommending_officer_id`),
  ADD KEY `fk_pr_approved_by` (`approved_by`),
  ADD KEY `purchase_requests_recommended_by_foreign` (`recommended_by`),
  ADD KEY `purchase_requests_fund_allocation_id_foreign` (`fund_allocation_id`);');
      DB::statement('ALTER TABLE `purchase_requests`
  ADD CONSTRAINT `purchase_requests_fund_allocation_id_foreign`
  FOREIGN KEY (`fund_allocation_id`) REFERENCES `fund_allocations` (`id`) ON DELETE SET NULL;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_requests');
    }
};
