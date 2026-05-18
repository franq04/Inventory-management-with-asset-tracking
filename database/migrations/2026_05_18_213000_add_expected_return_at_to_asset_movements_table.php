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
        if (! Schema::hasTable('asset_movements')) {
            return;
        }

        if (Schema::hasColumn('asset_movements', 'expected_return_at')) {
            return;
        }

        Schema::table('asset_movements', function (Blueprint $table): void {
            $table->date('expected_return_at')->nullable()->after('effective_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('asset_movements')) {
            return;
        }

        if (! Schema::hasColumn('asset_movements', 'expected_return_at')) {
            return;
        }

        Schema::table('asset_movements', function (Blueprint $table): void {
            $table->dropColumn('expected_return_at');
        });
    }
};
