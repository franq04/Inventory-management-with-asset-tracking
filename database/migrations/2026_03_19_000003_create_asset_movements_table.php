<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('asset_movements')) {
            Schema::create('asset_movements', function (Blueprint $table): void {
                $table->bigIncrements('movement_id');
                $table->string('property_no', 100)->collation('utf8mb4_general_ci');
                $table->unsignedInteger('from_location_id')->nullable();
                $table->unsignedInteger('to_location_id')->nullable();
                $table->string('from_custodian_employee_id', 255)->collation('utf8mb4_general_ci')->nullable();
                $table->string('to_custodian_employee_id', 255)->collation('utf8mb4_general_ci')->nullable();
                $table->integer('from_division_id')->nullable();
                $table->integer('to_division_id')->nullable();
                $table->integer('from_section_id')->nullable();
                $table->integer('to_section_id')->nullable();
                $table->enum('movement_type', [
                    'initial_assignment',
                    'transfer',
                    'relocation',
                    'inventory_correction',
                    'maintenance_out',
                    'maintenance_in',
                    'disposal',
                    'write_off',
                ])->default('transfer');
                $table->string('reason_code', 100)->nullable();
                $table->timestamp('effective_at')->useCurrent();
                $table->unsignedBigInteger('recorded_by')->nullable();
                $table->string('source_table', 50)->nullable();
                $table->string('source_record_id', 100)->nullable();
                $table->text('remarks')->nullable();
                $table->timestamps();

                $table->index(['property_no', 'effective_at'], 'idx_asset_movements_property_timeline');
                $table->index(['to_division_id', 'to_section_id', 'to_location_id'], 'idx_asset_movements_target_org');
                $table->index('recorded_by', 'idx_asset_movements_recorded_by');
            });
        }

        // Backfill baseline movement records for existing assets.
        DB::statement("INSERT INTO asset_movements (
                property_no,
                to_location_id,
                to_custodian_employee_id,
                to_division_id,
                to_section_id,
                movement_type,
                reason_code,
                effective_at,
                recorded_by,
                source_table,
                source_record_id,
                remarks,
                created_at,
                updated_at
            )
            SELECT
                p.property_no,
                p.current_location_id,
                COALESCE(p.current_custodian_employee_id, p.accountable_officer_id),
                p.assigned_division_id,
                p.assigned_section_id,
                'initial_assignment',
                'backfill_baseline',
                COALESCE(p.last_movement_at, NOW()),
                NULL,
                'pqs',
                p.property_no,
                'Baseline movement created during asset movement rollout.',
                NOW(),
                NOW()
            FROM pqs p
            WHERE NOT EXISTS (
                SELECT 1
                FROM asset_movements am
                WHERE am.property_no COLLATE utf8mb4_general_ci = p.property_no COLLATE utf8mb4_general_ci
            )");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_movements');
    }
};
