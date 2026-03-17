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
        if (Schema::hasTable('purchase_orders')) {
            return;
        }

        DB::statement('CREATE TABLE `purchase_orders` (
  `po_no` varchar(50) NOT NULL,
  `pr_no` varchar(50) NOT NULL,
  `supplier_id` varchar(50) DEFAULT NULL,
  `supplier_contract_no` varchar(100) DEFAULT NULL,
  `order_date` date NOT NULL,
  `awarded_at` date DEFAULT NULL,
  `mode_of_procurement` varchar(100) DEFAULT NULL,
  `procurement_activity_ref` varchar(100) DEFAULT NULL,
  `place_of_delivery` varchar(255) DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `delivery_term` varchar(100) DEFAULT NULL,
  `payment_term` varchar(100) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `fund_cluster` varchar(50) DEFAULT NULL,
  `funds_available` decimal(15,2) DEFAULT NULL,
  `ors_burs_no` varchar(100) DEFAULT NULL,
  `ors_burs_date` date DEFAULT NULL,
  `ors_burs_amount` decimal(15,2) DEFAULT NULL,
  `amount_in_words` varchar(255) DEFAULT NULL,
  `conforme_name` varchar(150) DEFAULT NULL,
  `conforme_date` date DEFAULT NULL,
  `authorized_by` bigint(20) UNSIGNED DEFAULT NULL,
  `ordered_by` bigint(20) UNSIGNED DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;');
        DB::statement('ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`po_no`),
  ADD KEY `fk_po_pr_no` (`pr_no`),
  ADD KEY `fk_po_supplier` (`supplier_id`),
  ADD KEY `fk_po_authorized_by` (`authorized_by`),
  ADD KEY `fk_po_ordered_by` (`ordered_by`),
  ADD KEY `fk_po_status` (`status_id`);');
          DB::statement('ALTER TABLE `purchase_orders`
      ADD KEY `idx_po_order_created` (`order_date`,`created_at`);');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
