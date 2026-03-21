<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            StatusSeeder::class,
            DivisionSectionPositionSeeder::class,
            RoleAccountsSeeder::class,
            PhysicalLocationSeeder::class,
        ]);
    }
}
