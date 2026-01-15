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
            // Requested default accounts (password == username)
            ['username' => 'employee', 'plain_password' => 'employee', 'role' => 'employee'],
            ['username' => 'custodian', 'plain_password' => 'custodian', 'role' => 'custodian'],
            ['username' => 'division_head', 'plain_password' => 'division_head', 'role' => 'division_head'],
            ['username' => 'IAC', 'plain_password' => 'IAC', 'role' => 'iac'],
            ['username' => 'BAC', 'plain_password' => 'BAC', 'role' => 'bac'],

            // Existing role-specific accounts
            ['username' => 'division.head', 'plain_password' => 'DivHead@2025', 'role' => 'division_head'],
            ['username' => 'iac.lead', 'plain_password' => 'IACLead@2025', 'role' => 'iac'],
            ['username' => 'iac.member', 'plain_password' => 'IACMember@2025', 'role' => 'iac'],
        ];

        $nextAccountId = ((int) Account::query()->max('account_id')) + 1;

        foreach ($accounts as $accountData) {
            $account = Account::query()->where('username', $accountData['username'])->first();

            $payload = [
                'username' => $accountData['username'],
                'password' => Hash::make($accountData['plain_password']),
                'role' => $accountData['role'],
            ];

            if ($account) {
                $account->fill($payload)->save();
                continue;
            }

            Account::query()->create([
                'account_id' => $nextAccountId++,
                ...$payload,
            ]);
        }
    }
}
