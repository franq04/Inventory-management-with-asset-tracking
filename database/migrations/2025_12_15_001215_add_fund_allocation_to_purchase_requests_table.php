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
        // Columns fund_allocation_id and funds_available already exist in the table
        // This migration just adds the foreign key constraint
        Schema::table('purchase_requests', function (Blueprint $table) {
            // Try to add foreign key (will skip if it exists)
            try {
                $table->foreign('fund_allocation_id')->references('id')->on('fund_allocations')->onDelete('set null');
            } catch (\Exception $e) {
                // Foreign key already exists, skip
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropForeign(['fund_allocation_id']);
            $table->dropColumn('fund_allocation_id');
            // Don't drop funds_available as it existed before this migration
        });
    }
};
