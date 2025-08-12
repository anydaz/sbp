<?php

namespace App\Services;

use App\Models\Account;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\DB;

class AccountService
{
    public function getAllAccounts()
    {
        $accounts = Account::where('parent_account_id', '!=', null)->get();

        foreach ($accounts as $account) {
            // Get total debits and credits
            $totals = JournalEntry::where('account_id', $account->id)
                ->select(
                    DB::raw('SUM(debit) as total_debit'),
                    DB::raw('SUM(credit) as total_credit')
                )
                ->first();

            $account->total_debit = $totals->total_debit ?? 0;
            $account->total_credit = $totals->total_credit ?? 0;

            // Calculate ending balance based on account type
            // For asset and expense accounts, debit increases the balance
            // For liability, equity, and revenue accounts, credit increases the balance
            if (in_array($account->type, ['asset', 'expense'])) {
                $account->balance = $account->starting_balance + ($account->total_debit - $account->total_credit);
            } else {
                $account->balance = $account->starting_balance + ($account->total_credit - $account->total_debit);
            }
        }

        // Group accounts by type
        return $accounts->groupBy('type');
    }

    public function getAccountDetails($id)
    {
        $account = Account::findOrFail($id);

        // Get all journal entries for this account
        $entries = JournalEntry::where('account_id', $id)
            ->with(['batch' => function($q) {
                $q->select('id', 'description', 'date', 'reference_type', 'reference_id');
            }])
            ->orderBy('date', 'asc')
            ->get();

        $runningBalance = $account->starting_balance;
        foreach ($entries as $entry) {
            if (in_array($account->type, ['asset', 'expense'])) {
                $runningBalance += ($entry->debit - $entry->credit);
            } else {
                $runningBalance += ($entry->credit - $entry->debit);
            }
            $entry->running_balance = $runningBalance;
        }

        $account->entries = $entries;
        return $account;
    }

    public function getAccountsByType($type)
    {
        return Account::where('type', $type)
            ->where('parent_account_id', '!=', null)
            ->orderBy('code')
            ->get();
    }
}
