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
        if (Schema::hasTable('physical_locations')) {
            return;
        }

        Schema::create('physical_locations', function (Blueprint $table): void {
            $table->increments('location_id');
            $table->string('location_name', 255);
            $table->string('location_code', 64)->nullable();
            $table->enum('location_type', ['building', 'floor', 'room', 'storage', 'other'])->default('room');
            $table->unsignedInteger('parent_location_id')->nullable();
            $table->integer('division_id')->nullable();
            $table->integer('section_id')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('parent_location_id', 'idx_locations_parent');
            $table->index(['division_id', 'section_id'], 'idx_locations_division_section');
            $table->unique('location_code', 'uq_locations_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('physical_locations')) {
            return;
        }

        // Safety-first rollback: avoid destructive drops unless explicitly forced.
        $forceDrop = filter_var(env('MIGRATIONS_FORCE_DROP_EXISTING', false), FILTER_VALIDATE_BOOLEAN);
        $hasData = DB::table('physical_locations')->exists();
        $hasIncomingForeignKeys = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('REFERENCED_TABLE_NAME', 'physical_locations')
            ->exists();

        if (($hasData || $hasIncomingForeignKeys) && ! $forceDrop) {
            return;
        }

        Schema::drop('physical_locations');
    }
};
