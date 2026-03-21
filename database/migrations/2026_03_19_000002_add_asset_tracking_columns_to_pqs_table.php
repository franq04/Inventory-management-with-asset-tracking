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
        if (! Schema::hasTable('pqs')) {
            return;
        }

        Schema::table('pqs', function (Blueprint $table): void {
            if (! Schema::hasColumn('pqs', 'current_location_id')) {
                $table->unsignedInteger('current_location_id')->nullable()->after('cat_id');
            }

            if (! Schema::hasColumn('pqs', 'current_custodian_employee_id')) {
                $table->string('current_custodian_employee_id', 255)->nullable()->after('current_location_id');
            }

            if (! Schema::hasColumn('pqs', 'assigned_division_id')) {
                $table->integer('assigned_division_id')->nullable()->after('current_custodian_employee_id');
            }

            if (! Schema::hasColumn('pqs', 'assigned_section_id')) {
                $table->integer('assigned_section_id')->nullable()->after('assigned_division_id');
            }

            if (! Schema::hasColumn('pqs', 'asset_status')) {
                $table->enum('asset_status', ['active', 'transferred', 'disposed', 'lost', 'for_repair'])->default('active')->after('assigned_section_id');
            }

            if (! Schema::hasColumn('pqs', 'last_movement_at')) {
                $table->timestamp('last_movement_at')->nullable()->after('asset_status');
            }

            if (! Schema::hasColumn('pqs', 'last_inventory_date')) {
                $table->date('last_inventory_date')->nullable()->after('last_movement_at');
            }

            if (! Schema::hasColumn('pqs', 'last_inventoried_by')) {
                $table->string('last_inventoried_by', 255)->nullable()->after('last_inventory_date');
            }
        });

        Schema::table('pqs', function (Blueprint $table): void {
            $table->index('current_location_id', 'idx_pqs_current_location');
            $table->index('current_custodian_employee_id', 'idx_pqs_current_custodian');
            $table->index(['assigned_division_id', 'assigned_section_id'], 'idx_pqs_division_section');
            $table->index('asset_status', 'idx_pqs_asset_status');
            $table->index('last_movement_at', 'idx_pqs_last_movement');
        });

        // Baseline backfill for existing assets.
        DB::statement("UPDATE pqs p
            LEFT JOIN employees e ON e.employee_id = p.accountable_officer_id
            LEFT JOIN sections s ON s.section_id = e.section_id
            SET
                p.current_custodian_employee_id = COALESCE(p.current_custodian_employee_id, p.accountable_officer_id),
                p.assigned_section_id = COALESCE(p.assigned_section_id, e.section_id),
                p.assigned_division_id = COALESCE(p.assigned_division_id, s.division_id),
                p.asset_status = COALESCE(p.asset_status, 'active'),
                p.last_movement_at = COALESCE(p.last_movement_at, NOW())");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('pqs')) {
            return;
        }

        Schema::table('pqs', function (Blueprint $table): void {
            if (Schema::hasColumn('pqs', 'last_inventoried_by')) {
                $table->dropColumn('last_inventoried_by');
            }
            if (Schema::hasColumn('pqs', 'last_inventory_date')) {
                $table->dropColumn('last_inventory_date');
            }
            if (Schema::hasColumn('pqs', 'last_movement_at')) {
                $table->dropColumn('last_movement_at');
            }
            if (Schema::hasColumn('pqs', 'asset_status')) {
                $table->dropColumn('asset_status');
            }
            if (Schema::hasColumn('pqs', 'assigned_section_id')) {
                $table->dropColumn('assigned_section_id');
            }
            if (Schema::hasColumn('pqs', 'assigned_division_id')) {
                $table->dropColumn('assigned_division_id');
            }
            if (Schema::hasColumn('pqs', 'current_custodian_employee_id')) {
                $table->dropColumn('current_custodian_employee_id');
            }
            if (Schema::hasColumn('pqs', 'current_location_id')) {
                $table->dropColumn('current_location_id');
            }
        });
    }
};
