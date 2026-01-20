<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add fields for BAC alternative suggestion workflow.
     * 
     * Workflow:
     * 1. BAC suggests alternative for an item (sets alternate_description, suggested_by, suggested_at)
     * 2. Requester is notified
     * 3. Requester responds: accept alternative OR wait until date for original
     * 4. BAC is notified of decision
     * 5. If waiting and date passes with no approval, item is removed
     */
    public function up(): void
    {
        Schema::table('purchase_request_items', function (Blueprint $table) {
            // Who suggested the alternative (BAC account_id)
            if (!Schema::hasColumn('purchase_request_items', 'suggested_by')) {
                $table->unsignedBigInteger('suggested_by')->nullable()->after('alternate_description');
            }
            
            // When the alternative was suggested
            if (!Schema::hasColumn('purchase_request_items', 'suggested_at')) {
                $table->timestamp('suggested_at')->nullable()->after('suggested_by');
            }
            
            // Original item description (preserved when alternative is accepted)
            if (!Schema::hasColumn('purchase_request_items', 'original_description')) {
                $table->string('original_description', 500)->nullable()->after('suggested_at');
            }
            
            // Whether this item was removed due to wait expiry
            if (!Schema::hasColumn('purchase_request_items', 'removed_at')) {
                $table->timestamp('removed_at')->nullable()->after('employee_wait_note');
            }
            
            // Reason for removal
            if (!Schema::hasColumn('purchase_request_items', 'removal_reason')) {
                $table->string('removal_reason', 255)->nullable()->after('removed_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_request_items', function (Blueprint $table) {
            $columns = ['suggested_by', 'suggested_at', 'original_description', 'removed_at', 'removal_reason'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('purchase_request_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
