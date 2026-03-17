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
        if (Schema::hasTable('purchase_order_items')) {
            return;
        }

        DB::statement('CREATE TABLE `purchase_order_items` (
  `poi_id` bigint(20) UNSIGNED NOT NULL,
  `po_no` varchar(50) NOT NULL,
  `pri_id` bigint(20) UNSIGNED DEFAULT NULL,
  `item_description` text NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit` varchar(100) NOT NULL,
  `unit_cost` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_cost` decimal(15,2) GENERATED ALWAYS AS (`quantity` * `unit_cost`) STORED,
  `remarks` varchar(255) DEFAULT NULL,
  `fulfillment_status` varchar(32) NOT NULL DEFAULT \'ordered\',
  `alternate_description` varchar(255) DEFAULT NULL,
  `employee_decision` varchar(32) DEFAULT NULL,
  `employee_decided_at` timestamp NULL DEFAULT NULL,
  `employee_wait_until` date DEFAULT NULL,
  `employee_wait_note` varchar(255) DEFAULT NULL,
  `received_at` timestamp NULL DEFAULT NULL,
  `received_by` bigint(20) UNSIGNED DEFAULT NULL,
  `receiving_note` text DEFAULT NULL,
  `inspection_status_id` int(11) DEFAULT NULL,
  `inspection_remarks` text DEFAULT NULL,
  `warranty_start` date DEFAULT NULL,
  `warranty_end` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;');
        DB::statement('ALTER TABLE `purchase_order_items`
  ADD PRIMARY KEY (`poi_id`),
  ADD KEY `fk_poi_po` (`po_no`),
  ADD KEY `fk_poi_pri` (`pri_id`),
  ADD KEY `fk_poi_inspection_status` (`inspection_status_id`),
  ADD KEY `purchase_order_items_received_by_foreign` (`received_by`);');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
};
