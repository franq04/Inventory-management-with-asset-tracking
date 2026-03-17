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
        if (Schema::hasTable('inspection_report_items')) {
            return;
        }

        DB::statement('CREATE TABLE `inspection_report_items` (
  `ia_item_id` bigint(20) UNSIGNED NOT NULL,
  `ia_no` varchar(50) NOT NULL,
  `po_item_id` bigint(20) UNSIGNED NOT NULL,
  `quantity_delivered` int(11) NOT NULL DEFAULT 0,
  `quantity_accepted` int(11) NOT NULL DEFAULT 0,
  `quantity_rejected` int(11) NOT NULL DEFAULT 0,
  `inspection_status_id` int(11) DEFAULT NULL,
  `inspection_remarks` text DEFAULT NULL,
  `property_no` varchar(100) DEFAULT NULL,
  `warranty_expiration` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;');
        DB::statement('ALTER TABLE `inspection_report_items`
  ADD PRIMARY KEY (`ia_item_id`),
  ADD UNIQUE KEY `inspection_report_items_property_no_unique` (`property_no`),
  ADD KEY `fk_iai_ia` (`ia_no`),
  ADD KEY `fk_iai_poi` (`po_item_id`),
  ADD KEY `fk_iai_status` (`inspection_status_id`);');
          DB::statement('ALTER TABLE `inspection_report_items`
      ADD KEY `idx_iri_poi_status` (`po_item_id`,`inspection_status_id`);');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspection_report_items');
    }
};
