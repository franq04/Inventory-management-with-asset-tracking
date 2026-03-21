<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PhysicalLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('physical_locations')->count() > 0) {
            return;
        }

        $buildingId = DB::table('physical_locations')->insertGetId([
            'location_name' => 'BPI Main Building',
            'location_code' => 'BLDG-MAIN',
            'location_type' => 'building',
            'parent_location_id' => null,
            'division_id' => null,
            'section_id' => null,
            'description' => 'Primary building',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $floorId = DB::table('physical_locations')->insertGetId([
            'location_name' => 'Main Building Floor 1',
            'location_code' => 'BLDG-MAIN-F1',
            'location_type' => 'floor',
            'parent_location_id' => $buildingId,
            'division_id' => 6,
            'section_id' => null,
            'description' => 'First floor',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('physical_locations')->insert([
            [
                'location_name' => 'Property and Supply Office',
                'location_code' => 'ADMIN-PROP-RM1',
                'location_type' => 'room',
                'parent_location_id' => $floorId,
                'division_id' => 6,
                'section_id' => 12,
                'description' => 'Custodian room',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'location_name' => 'Accounting Office',
                'location_code' => 'ADMIN-ACCT-RM1',
                'location_type' => 'room',
                'parent_location_id' => $floorId,
                'division_id' => 6,
                'section_id' => 13,
                'description' => 'Accounting room',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
