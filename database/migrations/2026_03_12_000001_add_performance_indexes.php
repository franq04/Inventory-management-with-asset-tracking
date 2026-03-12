<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // notifications: polled every 60 s per user for unread count
        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['recipient_id', 'is_read', 'created_at'], 'idx_notif_recipient_read_date');
        });

        // audit_logs: dashboard "recent activity" ORDER BY log_time DESC LIMIT 5
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index('log_time', 'idx_audit_log_time');
        });

        // status_history: looked up by (table_name, record_id) in workflow service
        Schema::table('status_history', function (Blueprint $table) {
            $table->index(['table_name', 'record_id', 'changed_at'], 'idx_sh_table_record_date');
        });

        // pqs: ORDER BY date_acquired in dashboard recent assets
        Schema::table('pqs', function (Blueprint $table) {
            $table->index('date_acquired', 'idx_pqs_date_acquired');
        });

        // purchase_request_items: GROUP BY item_description in dashboard top-requested
        Schema::table('purchase_request_items', function (Blueprint $table) {
            $table->index('item_description', 'idx_pri_item_desc');
        });

        // purchase_orders: ORDER BY order_date, created_at on pipeline index
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->index(['order_date', 'created_at'], 'idx_po_order_created');
        });

        // inspection_report_items: composite for pipeline inspection lookup
        Schema::table('inspection_report_items', function (Blueprint $table) {
            $table->index(['po_item_id', 'inspection_status_id'], 'idx_iri_poi_status');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('idx_notif_recipient_read_date');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex('idx_audit_log_time');
        });

        Schema::table('status_history', function (Blueprint $table) {
            $table->dropIndex('idx_sh_table_record_date');
        });

        Schema::table('pqs', function (Blueprint $table) {
            $table->dropIndex('idx_pqs_date_acquired');
        });

        Schema::table('purchase_request_items', function (Blueprint $table) {
            $table->dropIndex('idx_pri_item_desc');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropIndex('idx_po_order_created');
        });

        Schema::table('inspection_report_items', function (Blueprint $table) {
            $table->dropIndex('idx_iri_poi_status');
        });
    }
};
