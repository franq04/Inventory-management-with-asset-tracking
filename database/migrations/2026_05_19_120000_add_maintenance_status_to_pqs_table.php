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
        if (! Schema::hasTable('pqs')) {
            return;
        }

        if (! Schema::hasColumn('pqs', 'asset_status')) {
            return;
        }

        DB::statement("ALTER TABLE pqs MODIFY COLUMN asset_status ENUM('active','transferred','disposed','lost','for_repair','maintenance') NOT NULL DEFAULT 'active'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('pqs')) {
            return;
        }

        if (! Schema::hasColumn('pqs', 'asset_status')) {
            return;
        }

        DB::statement("ALTER TABLE pqs MODIFY COLUMN asset_status ENUM('active','transferred','disposed','lost','for_repair') NOT NULL DEFAULT 'active'");
    }
};
