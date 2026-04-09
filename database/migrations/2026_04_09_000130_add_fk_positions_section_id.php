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
        if (! Schema::hasTable('positions') || ! Schema::hasTable('sections')) {
            return;
        }

        if (! Schema::hasColumn('positions', 'section_id') || ! Schema::hasColumn('sections', 'section_id')) {
            return;
        }

        $hasConstraint = DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'positions')
            ->where('CONSTRAINT_NAME', 'fk_positions_section')
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->exists();

        if ($hasConstraint) {
            return;
        }

        $orphanCount = DB::table('positions as p')
            ->leftJoin('sections as s', 's.section_id', '=', 'p.section_id')
            ->whereNull('s.section_id')
            ->count();

        if ($orphanCount > 0) {
            throw new RuntimeException('Cannot add fk_positions_section: positions.section_id contains orphan values.');
        }

        Schema::table('positions', function (Blueprint $table): void {
            $table->foreign('section_id', 'fk_positions_section')
                ->references('section_id')
                ->on('sections')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('positions')) {
            return;
        }

        $hasConstraint = DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'positions')
            ->where('CONSTRAINT_NAME', 'fk_positions_section')
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->exists();

        if (! $hasConstraint) {
            return;
        }

        Schema::table('positions', function (Blueprint $table): void {
            $table->dropForeign('fk_positions_section');
        });
    }
};
