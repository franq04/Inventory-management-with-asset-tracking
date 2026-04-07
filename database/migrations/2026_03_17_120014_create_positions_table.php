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
        if (Schema::hasTable('positions')) {
            return;
        }

          DB::statement('CREATE TABLE `positions` (
      `position_id` int(11) NOT NULL,
      `position_title` varchar(255) NOT NULL,
      `section_id` int(11) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;');
          DB::statement('ALTER TABLE `positions`
      ADD PRIMARY KEY (`position_id`),
      ADD KEY `idx_positions_section` (`section_id`);');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};
