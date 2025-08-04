<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // ADD ASSET ACCOUNTS
        $currentAsset = DB::table('accounts')->insertGetId([
            'code' => '1000',
            'name' => 'Current Assets',
            'type' => 'asset',
            'parent_account_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);


        DB::table('accounts')->insert([
            [
                'code' => '1001',
                'name' => 'Cash',
                'type' => 'asset',
                'parent_account_id' => $currentAsset,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => '1002',
                'name' => 'Bank',
                'type' => 'asset',
                'parent_account_id' => $currentAsset,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => '1003',
                'name' => 'Accounts Receivable',
                'type' => 'asset',
                'parent_account_id' => $currentAsset,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => '1004',
                'name' => 'Inventory',
                'type' => 'asset',
                'parent_account_id' => $currentAsset,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // ADD LIABILITY ACCOUNTS
        $currentLiability = DB::table('accounts')->insertGetId([
            'code' => '2000',
            'name' => 'Current Liabilities',
            'type' => 'liability',
            'parent_account_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('accounts')->insert([
            [
                'code' => '2001',
                'name' => 'Accounts Payable',
                'type' => 'liability',
                'parent_account_id' => $currentLiability,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // ADD EQUITY ACCOUNTS
        $equity = DB::table('accounts')->insertGetId([
            'code' => '3000',
            'name' => 'Equity',
            'type' => 'equity',
            'parent_account_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('accounts')->insert([
            [
                'code' => '3001',
                'name' => 'Owner\'s Equity',
                'type' => 'equity',
                'parent_account_id' => $equity,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => '3002',
                'name' => 'Retained Earnings',
                'type' => 'equity',
                'parent_account_id' => $equity,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => '3003',
                'name' => 'Dividends',
                'type' => 'equity',
                'parent_account_id' => $equity,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // ADD REVENUE ACCOUNTS
        $revenue = DB::table('accounts')->insertGetId([
            'code' => '4000',
            'name' => 'Revenue',
            'type' => 'revenue',
            'parent_account_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('accounts')->insert([
            [
                'code' => '4001',
                'name' => 'Sales Revenue',
                'type' => 'revenue',
                'parent_account_id' => $revenue,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => '4002',
                'name' => 'Interest Revenue',
                'type' => 'revenue',
                'parent_account_id' => $revenue,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => '4003',
                'name' => 'Other Revenue',
                'type' => 'revenue',
                'parent_account_id' => $revenue,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // ADD COST OF GOODS SOLD ACCOUNT
        DB::table('accounts')->insert([
            [
                'code' => '5000',
                'name' => 'Cost of Goods Sold',
                'type' => 'expense',
                'parent_account_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],

        ]);

        // ADD EXPENSE ACCOUNTS
        $expenses = DB::table('accounts')->insertGetId([
            'code' => '6000',
            'name' => 'Expenses',
            'type' => 'expense',
            'parent_account_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('accounts')->insert([
            [
                'code' => '6001',
                'name' => 'Salaries Expense',
                'type' => 'expense',
                'parent_account_id' => $expenses,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => '6002',
                'name' => 'Rent Expense',
                'type' => 'expense',
                'parent_account_id' => $expenses,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => '6003',
                'name' => 'Utilities Expense',
                'type' => 'expense',
                'parent_account_id' => $expenses,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => '6004',
                'name' => 'Depreciation Expense',
                'type' => 'expense',
                'parent_account_id' => $expenses,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => '6005',
                'name' => 'Bank Fees',
                'type' => 'expense',
                'parent_account_id' => $expenses,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
