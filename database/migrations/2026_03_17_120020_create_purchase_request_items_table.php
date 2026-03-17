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
        if (Schema::hasTable('purchase_request_items')) {
            return;
        }

        DB::statement('CREATE TABLE `purchase_request_items` (
  `pri_id` bigint(20) UNSIGNED NOT NULL,
  `pr_no` varchar(50) NOT NULL,
  `item_description` text NOT NULL,
  `item_type` enum(\'consumable\',\'non-consumable\') NOT NULL DEFAULT \'consumable\',
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit` varchar(100) NOT NULL,
  `stock_number` varchar(100) DEFAULT NULL,
  `estimated_unit_cost` decimal(15,2) NOT NULL DEFAULT 0.00,
  `estimated_total_cost` decimal(15,2) GENERATED ALWAYS AS (`quantity` * `estimated_unit_cost`) STORED,
  `remarks` varchar(255) DEFAULT NULL,
  `fulfillment_status` enum(\'pending\',\'ordered\',\'unavailable\',\'alternative\',\'waiting\',\'fulfilled\',\'expired\') NOT NULL DEFAULT \'pending\',
  `alternate_description` varchar(255) DEFAULT NULL,
  `suggested_by` int(10) UNSIGNED DEFAULT NULL,
  `suggested_at` timestamp NULL DEFAULT NULL,
  `original_description` varchar(500) DEFAULT NULL,
  `employee_decision` varchar(20) DEFAULT NULL,
  `employee_decided_at` timestamp NULL DEFAULT NULL,
  `employee_wait_until` date DEFAULT NULL,
  `employee_wait_note` varchar(255) DEFAULT NULL,
  `removed_at` timestamp NULL DEFAULT NULL,
  `removal_reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;');
        DB::statement('ALTER TABLE `purchase_request_items`
  ADD PRIMARY KEY (`pri_id`),
  ADD KEY `fk_pr_items_pr` (`pr_no`);');
          DB::statement('ALTER TABLE `purchase_request_items`
      ADD KEY `idx_pri_item_desc` (`item_description`(191));');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_request_items');
    }
};
