<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('accounts')) {
            return;
        }

        try {
            $column = DB::selectOne("SHOW COLUMNS FROM `accounts` LIKE 'account_id'");
            $hasAutoIncrement = $column && isset($column->Extra) && str_contains((string) $column->Extra, 'auto_increment');

            $primaryKey = DB::selectOne("SHOW KEYS FROM `accounts` WHERE Key_name = 'PRIMARY'");
            $hasPrimaryKey = $primaryKey !== null;

            if (!$hasPrimaryKey) {
                DB::statement('ALTER TABLE `accounts` ADD PRIMARY KEY (`account_id`)');
            }

            if (!$hasAutoIncrement) {
                DB::statement('ALTER TABLE `accounts` MODIFY `account_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT');
            }
        } catch (QueryException) {
            // Some local MariaDB/MySQL installations can fail ALTER TABLE due to system-table issues.
            // Seeding is still supported by explicitly setting account_id in seeders.
            return;
        }
    }

    public function down(): void
    {
        // Intentionally left blank: reverting AUTO_INCREMENT/PK safely is app-specific.
    }
};
