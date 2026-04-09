<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('positions')) {
            return;
        }

        Schema::create('positions', function (Blueprint $table): void {
            $table->integer('position_id');
            $table->string('position_title', 255);
            $table->integer('section_id')->nullable();

            $table->primary('position_id');
            $table->index('section_id', 'idx_positions_section');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('positions')) {
            return;
        }

        // Safety-first rollback: avoid destructive drops unless explicitly forced.
        $forceDrop = filter_var(env('MIGRATIONS_FORCE_DROP_EXISTING', false), FILTER_VALIDATE_BOOLEAN);
        $hasData = DB::table('positions')->exists();
        $hasIncomingForeignKeys = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('REFERENCED_TABLE_NAME', 'positions')
            ->exists();

        if (($hasData || $hasIncomingForeignKeys) && ! $forceDrop) {
            return;
        }

        Schema::drop('positions');
    }
};
