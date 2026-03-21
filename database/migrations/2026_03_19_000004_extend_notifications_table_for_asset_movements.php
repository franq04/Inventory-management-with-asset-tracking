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
        if (! Schema::hasTable('notifications')) {
            return;
        }

        DB::statement("ALTER TABLE notifications MODIFY COLUMN table_name ENUM('purchase_requests','purchase_orders','inspection_acceptance','asset_movements','pqs') DEFAULT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('notifications')) {
            return;
        }

        DB::statement("ALTER TABLE notifications MODIFY COLUMN table_name ENUM('purchase_requests','purchase_orders','inspection_acceptance') DEFAULT NULL");
    }
};
