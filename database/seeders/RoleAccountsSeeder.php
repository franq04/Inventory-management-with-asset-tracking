<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleAccountsSeeder extends Seeder
{
    /**
     * Seed the application's database with role-specific accounts.
     */
    public function run(): void
    {
        $accounts = [
            [
                'username' => 'division.head',
                'password' => Hash::make('DivHead@2025'),
                'role' => 'division_head',
            ],
            [
                'username' => 'iac.lead',
                'password' => Hash::make('IACLead@2025'),
                'role' => 'iac',
            ],
            [
                'username' => 'iac.member',
                'password' => Hash::make('IACMember@2025'),
                'role' => 'iac',
            ],
        ];

        foreach ($accounts as $accountData) {
            Account::query()->updateOrCreate(
                ['username' => $accountData['username']],
                $accountData
            );
        }
    }
}
