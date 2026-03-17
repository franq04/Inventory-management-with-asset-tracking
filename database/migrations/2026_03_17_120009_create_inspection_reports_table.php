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
        if (Schema::hasTable('inspection_reports')) {
            return;
        }

        DB::statement('CREATE TABLE `inspection_reports` (
  `ia_no` varchar(50) NOT NULL,
  `po_no` varchar(50) NOT NULL,
  `fund_cluster` varchar(50) DEFAULT NULL,
  `inspection_date` date NOT NULL DEFAULT curdate(),
  `accepted_date` date DEFAULT NULL,
  `invoice_no` varchar(100) DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `inspected_by` bigint(20) UNSIGNED DEFAULT NULL,
  `accepted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `overall_status_id` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `responsibility_center_code` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;');
        DB::statement('ALTER TABLE `inspection_reports`
  ADD PRIMARY KEY (`ia_no`),
  ADD KEY `fk_ia_po` (`po_no`),
  ADD KEY `fk_ia_status` (`overall_status_id`),
  ADD KEY `fk_ia_inspected_by` (`inspected_by`),
  ADD KEY `fk_ia_accepted_by` (`accepted_by`);');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspection_reports');
    }
};
