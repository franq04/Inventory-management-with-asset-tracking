<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE asset_movements MODIFY COLUMN movement_type ENUM('initial_assignment','transfer','turnover','relocation','inventory_correction','maintenance_out','maintenance_in','disposal','write_off') NOT NULL DEFAULT 'transfer'");

        DB::statement("UPDATE asset_movements
            SET movement_type = 'turnover'
            WHERE movement_type = 'transfer'
              AND reason_code = 'employee_turnover'
              AND source_table = 'pqs_turnover_batch'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("UPDATE asset_movements
            SET movement_type = 'transfer'
            WHERE movement_type = 'turnover'");

        DB::statement("ALTER TABLE asset_movements MODIFY COLUMN movement_type ENUM('initial_assignment','transfer','relocation','inventory_correction','maintenance_out','maintenance_in','disposal','write_off') NOT NULL DEFAULT 'transfer'");
    }
};
