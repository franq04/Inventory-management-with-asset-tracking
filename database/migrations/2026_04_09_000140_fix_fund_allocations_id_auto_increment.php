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
        if (!Schema::hasTable('fund_allocations') || !Schema::hasColumn('fund_allocations', 'id')) {
            return;
        }

        DB::statement('ALTER TABLE `fund_allocations` MODIFY `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('fund_allocations') || !Schema::hasColumn('fund_allocations', 'id')) {
            return;
        }

        DB::statement('ALTER TABLE `fund_allocations` MODIFY `id` BIGINT(20) UNSIGNED NOT NULL');
    }
};
