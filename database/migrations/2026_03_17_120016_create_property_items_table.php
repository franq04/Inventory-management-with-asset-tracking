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
        if (Schema::hasTable('property_items')) {
            return;
        }

        DB::statement('CREATE TABLE `property_items` (
  `property_item_id` bigint(20) UNSIGNED NOT NULL,
  `ia_item_id` bigint(20) UNSIGNED DEFAULT NULL,
  `po_item_id` bigint(20) UNSIGNED DEFAULT NULL,
  `property_no` varchar(255) DEFAULT NULL,
  `item_description` varchar(255) NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `unit` varchar(100) DEFAULT NULL,
  `acquisition_cost` decimal(15,2) NOT NULL DEFAULT 0.00,
  `acquisition_date` date DEFAULT NULL,
  `warranty_end` date DEFAULT NULL,
  `category_id` varchar(50) DEFAULT NULL,
  `subcategory_id` varchar(50) DEFAULT NULL,
  `custodian_employee_id` varchar(255) DEFAULT NULL,
  `item_type` enum(\'consumable\',\'non-consumable\') NOT NULL DEFAULT \'non-consumable\',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
        DB::statement('ALTER TABLE `property_items`
  ADD PRIMARY KEY (`property_item_id`),
  ADD KEY `property_items_ia_item_id_foreign` (`ia_item_id`),
  ADD KEY `property_items_po_item_id_foreign` (`po_item_id`);');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_items');
    }
};
