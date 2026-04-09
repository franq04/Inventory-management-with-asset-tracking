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
        if (! Schema::hasTable('positions') || ! Schema::hasColumn('positions', 'section_id')) {
            return;
        }

        if (! Schema::hasTable('sections') || ! Schema::hasColumn('sections', 'section_id')) {
            throw new RuntimeException('Cannot enforce positions.section_id NOT NULL because sections table is unavailable.');
        }

        $fallbackSectionId = DB::table('sections')->min('section_id');

        if ($fallbackSectionId === null) {
            $fallbackSectionId = ((int) DB::table('sections')->max('section_id')) + 1;

            DB::table('sections')->insert([
                'section_id' => $fallbackSectionId,
                'section_name' => 'Unassigned Section',
                'section_code' => 'UNASSIGNED',
                'division_id' => null,
                'description' => 'Auto-generated fallback section for positions.section_id migration.',
            ]);
        }

        DB::table('positions')
            ->whereNull('section_id')
            ->update(['section_id' => $fallbackSectionId]);

        DB::statement('ALTER TABLE `positions` MODIFY `section_id` INT(11) NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('positions') || ! Schema::hasColumn('positions', 'section_id')) {
            return;
        }

        DB::statement('ALTER TABLE `positions` MODIFY `section_id` INT(11) NULL');
    }
};
