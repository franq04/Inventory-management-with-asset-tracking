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
        if (!Schema::hasTable('physical_locations')) {
            return;
        }

        // Normalize orphan references before adding FKs.
        DB::statement(
            'UPDATE physical_locations pl '
            .'LEFT JOIN physical_locations parent ON parent.location_id = pl.parent_location_id '
            .'SET pl.parent_location_id = NULL '
            .'WHERE pl.parent_location_id IS NOT NULL AND parent.location_id IS NULL'
        );

        DB::statement(
            'UPDATE physical_locations pl '
            .'LEFT JOIN divisions d ON d.division_id = pl.division_id '
            .'SET pl.division_id = NULL '
            .'WHERE pl.division_id IS NOT NULL AND d.division_id IS NULL'
        );

        DB::statement(
            'UPDATE physical_locations pl '
            .'LEFT JOIN sections s ON s.section_id = pl.section_id '
            .'SET pl.section_id = NULL '
            .'WHERE pl.section_id IS NOT NULL AND s.section_id IS NULL'
        );

        if (Schema::hasTable('pqs')) {
            DB::statement(
                'UPDATE pqs p '
                .'LEFT JOIN physical_locations l ON l.location_id = p.current_location_id '
                .'SET p.current_location_id = NULL '
                .'WHERE p.current_location_id IS NOT NULL AND l.location_id IS NULL'
            );
        }

        if (Schema::hasTable('asset_movements')) {
            DB::statement(
                'UPDATE asset_movements am '
                .'LEFT JOIN physical_locations lf ON lf.location_id = am.from_location_id '
                .'SET am.from_location_id = NULL '
                .'WHERE am.from_location_id IS NOT NULL AND lf.location_id IS NULL'
            );

            DB::statement(
                'UPDATE asset_movements am '
                .'LEFT JOIN physical_locations lt ON lt.location_id = am.to_location_id '
                .'SET am.to_location_id = NULL '
                .'WHERE am.to_location_id IS NOT NULL AND lt.location_id IS NULL'
            );
        }

        Schema::table('physical_locations', function (Blueprint $table): void {
            $table->foreign('parent_location_id', 'fk_locations_parent')
                ->references('location_id')
                ->on('physical_locations')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('division_id', 'fk_locations_division')
                ->references('division_id')
                ->on('divisions')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('section_id', 'fk_locations_section')
                ->references('section_id')
                ->on('sections')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });

        if (Schema::hasTable('pqs')) {
            Schema::table('pqs', function (Blueprint $table): void {
                $table->foreign('current_location_id', 'fk_pqs_current_location')
                    ->references('location_id')
                    ->on('physical_locations')
                    ->nullOnDelete()
                    ->cascadeOnUpdate();
            });
        }

        if (Schema::hasTable('asset_movements')) {
            Schema::table('asset_movements', function (Blueprint $table): void {
                $table->foreign('from_location_id', 'fk_asset_movements_from_location')
                    ->references('location_id')
                    ->on('physical_locations')
                    ->nullOnDelete()
                    ->cascadeOnUpdate();

                $table->foreign('to_location_id', 'fk_asset_movements_to_location')
                    ->references('location_id')
                    ->on('physical_locations')
                    ->nullOnDelete()
                    ->cascadeOnUpdate();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('asset_movements')) {
            Schema::table('asset_movements', function (Blueprint $table): void {
                $table->dropForeign('fk_asset_movements_from_location');
                $table->dropForeign('fk_asset_movements_to_location');
            });
        }

        if (Schema::hasTable('pqs')) {
            Schema::table('pqs', function (Blueprint $table): void {
                $table->dropForeign('fk_pqs_current_location');
            });
        }

        if (!Schema::hasTable('physical_locations')) {
            return;
        }

        Schema::table('physical_locations', function (Blueprint $table): void {
            $table->dropForeign('fk_locations_parent');
            $table->dropForeign('fk_locations_division');
            $table->dropForeign('fk_locations_section');
        });
    }
};
