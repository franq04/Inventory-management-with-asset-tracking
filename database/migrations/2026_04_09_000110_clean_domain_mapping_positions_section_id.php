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
        if (! Schema::hasTable('positions') || ! Schema::hasTable('employees') || ! Schema::hasTable('sections')) {
            return;
        }

        DB::transaction(function (): void {
            $fallbackSectionId = DB::table('sections')
                ->where('section_name', 'Property & Supply Section')
                ->value('section_id');

            if ($fallbackSectionId === null) {
                $fallbackSectionId = DB::table('sections')->min('section_id');
            }

            if ($fallbackSectionId === null) {
                throw new RuntimeException('No sections found for positions.section_id mapping.');
            }

            $positions = DB::table('positions')->select('position_id')->orderBy('position_id')->get();

            foreach ($positions as $position) {
                $mappedSectionId = DB::table('employees')
                    ->where('position_id', $position->position_id)
                    ->whereNotNull('section_id')
                    ->select('section_id', DB::raw('COUNT(*) AS usage_count'))
                    ->groupBy('section_id')
                    ->orderByDesc('usage_count')
                    ->orderBy('section_id')
                    ->value('section_id');

                DB::table('positions')
                    ->where('position_id', $position->position_id)
                    ->update([
                        'section_id' => $mappedSectionId ?? $fallbackSectionId,
                    ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally no-op: this migration normalizes data mappings only.
    }
};
