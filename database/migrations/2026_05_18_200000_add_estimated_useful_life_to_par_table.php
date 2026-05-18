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
        if (! Schema::hasTable('par')) {
            return;
        }

        if (Schema::hasColumn('par', 'estimated_useful_life')) {
            return;
        }

        Schema::table('par', function (Blueprint $table): void {
            $table->string('estimated_useful_life', 100)->nullable()->after('amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('par')) {
            return;
        }

        if (! Schema::hasColumn('par', 'estimated_useful_life')) {
            return;
        }

        Schema::table('par', function (Blueprint $table): void {
            $table->dropColumn('estimated_useful_life');
        });
    }
};
