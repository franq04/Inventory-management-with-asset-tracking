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
        if (! Schema::hasTable('positions') || ! Schema::hasTable('sections')) {
            return;
        }

        $propertySupplySectionId = DB::table('sections')
            ->where('section_name', 'Property & Supply Section')
            ->value('section_id');

        $accountingSectionId = DB::table('sections')
            ->where('section_name', 'Accounting Section')
            ->value('section_id');

        if ($propertySupplySectionId === null) {
            return;
        }

        // Explicit business mapping for core inventory roles.
        DB::table('positions')
            ->whereIn('position_title', [
                'Property Custodian',
                'Supply Officer I',
                'Laboratory Technician II',
                'Division Chief',
            ])
            ->update(['section_id' => $propertySupplySectionId]);

        if ($accountingSectionId !== null) {
            DB::table('positions')
                ->where('position_title', 'Section Chief')
                ->update(['section_id' => $accountingSectionId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: refinement migration to correct business mapping.
    }
};
