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
        if (Schema::hasTable('pqs')) {
            return;
        }

        DB::statement('CREATE TABLE `pqs` (
  `property_no` varchar(100) NOT NULL,
  `article` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `serial_number` varchar(255) DEFAULT NULL,
  `date_acquired` date NOT NULL,
  `unit_value` decimal(15,2) NOT NULL,
  `unit` varchar(100) NOT NULL,
  `on_hand_per_count` int(11) NOT NULL,
  `total_value` decimal(15,2) NOT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `accountable_officer_id` varchar(255) DEFAULT NULL,
  `cat_id` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;');
        DB::statement('ALTER TABLE `pqs`
  ADD PRIMARY KEY (`property_no`),
  ADD UNIQUE KEY `pqs_serial_number_unique` (`serial_number`),
  ADD KEY `fk_pqs_category` (`cat_id`),
  ADD KEY `fk_pqs_accountable_officer` (`accountable_officer_id`);');
          DB::statement('ALTER TABLE `pqs`
      ADD KEY `idx_pqs_date_acquired` (`date_acquired`);');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pqs');
    }
};
