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
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->date('requested_delivery_date')->nullable()->after('purpose');
            $table->string('requested_delivery_term', 100)->nullable()->after('requested_delivery_date');
            $table->string('requested_payment_term', 100)->nullable()->after('requested_delivery_term');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropColumn([
                'requested_delivery_date',
                'requested_delivery_term',
                'requested_payment_term',
            ]);
        });
    }
};
