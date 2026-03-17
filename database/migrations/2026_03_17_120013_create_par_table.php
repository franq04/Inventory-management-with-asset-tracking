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
        if (Schema::hasTable('par')) {
            return;
        }

        DB::statement('CREATE TABLE `par` (
  `par_no` bigint(20) UNSIGNED NOT NULL,
  `property_no` varchar(100) NOT NULL,
  `article_desc` text NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit` varchar(100) NOT NULL,
  `date_acquired` date NOT NULL,
  `unit_value` decimal(15,2) NOT NULL,
  `amount` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;');
        DB::statement('ALTER TABLE `par`
  ADD PRIMARY KEY (`par_no`),
  ADD KEY `fk_par_pqs` (`property_no`);');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('par');
    }
};
