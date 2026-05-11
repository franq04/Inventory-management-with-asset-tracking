<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('inspection_report_items')) {
            return;
        }

        if (! Schema::hasColumn('inspection_report_items', 'serial_numbers')) {
            Schema::table('inspection_report_items', function (Blueprint $table) {
                $table->text('serial_numbers')->nullable()->after('inspection_remarks');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('inspection_report_items')) {
            return;
        }

        if (Schema::hasColumn('inspection_report_items', 'serial_numbers')) {
            Schema::table('inspection_report_items', function (Blueprint $table) {
                $table->dropColumn('serial_numbers');
            });
        }
    }
};
