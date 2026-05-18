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
        if (! Schema::hasTable('pqs') || ! Schema::hasTable('asset_movements')) {
            return;
        }

        DB::statement("UPDATE pqs p
            JOIN (
                SELECT am.property_no COLLATE utf8mb4_general_ci AS property_no, am.movement_type, am.reason_code
                FROM asset_movements am
                INNER JOIN (
                    SELECT property_no COLLATE utf8mb4_general_ci AS property_no, MAX(movement_id) AS max_id
                    FROM asset_movements
                    WHERE movement_type IN ('maintenance_out', 'maintenance_in')
                    GROUP BY property_no
                ) latest
                    ON latest.property_no = am.property_no COLLATE utf8mb4_general_ci
                    AND latest.max_id = am.movement_id
            ) last_condition
                ON last_condition.property_no = p.property_no COLLATE utf8mb4_general_ci
            SET p.asset_status = 'maintenance'
            WHERE p.asset_status = 'for_repair'
                AND last_condition.movement_type = 'maintenance_out'
                AND last_condition.reason_code = 'maintenance'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('pqs') || ! Schema::hasTable('asset_movements')) {
            return;
        }

        DB::statement("UPDATE pqs p
            JOIN (
                SELECT am.property_no COLLATE utf8mb4_general_ci AS property_no, am.movement_type, am.reason_code
                FROM asset_movements am
                INNER JOIN (
                    SELECT property_no COLLATE utf8mb4_general_ci AS property_no, MAX(movement_id) AS max_id
                    FROM asset_movements
                    WHERE movement_type IN ('maintenance_out', 'maintenance_in')
                    GROUP BY property_no
                ) latest
                    ON latest.property_no = am.property_no COLLATE utf8mb4_general_ci
                    AND latest.max_id = am.movement_id
            ) last_condition
                ON last_condition.property_no = p.property_no COLLATE utf8mb4_general_ci
            SET p.asset_status = 'for_repair'
            WHERE p.asset_status = 'maintenance'
                AND last_condition.movement_type = 'maintenance_out'
                AND last_condition.reason_code = 'maintenance'");
    }
};
