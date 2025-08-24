<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AccountBalance;
use App\Models\JournalEntry;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AccountBalanceService
{
    /**
     * Calculate and store account balances for a specific month
     */
    public function calculateAndStoreBalances(Carbon $date)
    {
        $periodIdentifier = $date->format('Y-m');

        // Get all accounts excluding parent accounts (accounts that have children)
        $accounts = Account::whereDoesntHave('childAccounts')->get();

        foreach ($accounts as $account) {
            $this->calculateAccountBalance($account, $date, $periodIdentifier);
        }
    }

    /**
     * Calculate balance for a specific account
     */
    protected function calculateAccountBalance($account, $endDate, $periodIdentifier)
    {
        // Get all journal entries for this account up to the end date
        // Include both original and reversal entries for complete balance calculation
        $journalSums = JournalEntry::where('account_id', $account->id)
            ->where('date', '<=', $endDate)
            ->selectRaw('
                SUM(debit) as total_debits,
                SUM(credit) as total_credits
            ')
            ->first();

        $totalDebits = $journalSums->total_debits ?? 0;
        $totalCredits = $journalSums->total_credits ?? 0;
        $netBalance = $totalDebits - $totalCredits;

        // Store or update the account balance
        AccountBalance::updateOrCreate(
            [
                'account_id' => $account->id,
                'period_identifier' => $periodIdentifier,
            ],
            [
                'balance_date' => $endDate,
                'debit_balance' => $totalDebits,
                'credit_balance' => $totalCredits,
                'net_balance' => $netBalance,
            ]
        );
    }

    /**
     * Get all asset balances for a month
     */
    public function getAssetBalances($periodIdentifier)
    {
        $periodIdentifier = $periodIdentifier ?? Carbon::now()->format('Y-m');

        return AccountBalance::with('account')
            ->where('period_identifier', $periodIdentifier)
            ->whereHas('account', function ($q) {
                $q->where('type', 'asset')
                  ->whereDoesntHave('childAccounts'); // Exclude parent accounts
            })
            ->get()
            ->map(function ($balance) {
                return [
                    'account_code' => $balance->account->code,
                    'account_name' => $balance->account->name,
                    'balance' => $balance->getNormalBalance(),
                    'debit_balance' => $balance->debit_balance,
                    'credit_balance' => $balance->credit_balance,
                ];
            });
    }

    /**
     * Get all liability balances for a month
     */
    public function getLiabilityBalances($periodIdentifier = null)
    {
        $periodIdentifier = $periodIdentifier ?? Carbon::now()->format('Y-m');

        return AccountBalance::with('account')
            ->where('period_identifier', $periodIdentifier)
            ->whereHas('account', function ($q) {
                $q->where('type', 'liability')
                  ->whereDoesntHave('childAccounts'); // Exclude parent accounts
            })
            ->get()
            ->map(function ($balance) {
                return [
                    'account_code' => $balance->account->code,
                    'account_name' => $balance->account->name,
                    'balance' => $balance->getNormalBalance(),
                    'debit_balance' => $balance->debit_balance,
                    'credit_balance' => $balance->credit_balance,
                ];
            });
    }

    /**
     * Get all equity balances for a month
     */
    public function getEquityBalances($periodIdentifier = null)
    {
        $periodIdentifier = $periodIdentifier ?? Carbon::now()->format('Y-m');

        $equityBalances = AccountBalance::with('account')
            ->where('period_identifier', $periodIdentifier)
            ->whereHas('account', function ($q) {
                $q->where('type', 'equity')
                  ->whereDoesntHave('childAccounts'); // Exclude parent accounts
            })
            ->get()
            ->map(function ($balance) {
                return [
                    'account_code' => $balance->account->code,
                    'account_name' => $balance->account->name,
                    'balance' => $balance->getNormalBalance(),
                    'debit_balance' => $balance->debit_balance,
                    'credit_balance' => $balance->credit_balance,
                ];
            });

        // Add current period net profit/loss to retained earnings
        $endDate = Carbon::createFromFormat('Y-m', $periodIdentifier)->endOfMonth();
        $netIncome = $this->calculateNetIncome($endDate);

        // Add net income as a separate equity item
        $equityBalances->push([
            'account_code' => 'NET',
            'account_name' => 'Net Income (Current Period)',
            'balance' => $netIncome,
            'debit_balance' => $netIncome < 0 ? abs($netIncome) : 0,
            'credit_balance' => $netIncome > 0 ? $netIncome : 0,
        ]);

        return $equityBalances;
    }

    /**
     * Get complete balance sheet data
     */
    public function getBalanceSheetData($periodIdentifier = null)
    {
        $assets = $this->getAssetBalances($periodIdentifier);
        $liabilities = $this->getLiabilityBalances($periodIdentifier);
        $equity = $this->getEquityBalances($periodIdentifier);

        $totalAssets = $assets->sum('balance');
        $totalLiabilities = $liabilities->sum('balance');
        $totalEquity = $equity->sum('balance');

        return [
            'assets' => [
                'accounts' => $assets,
                'total' => $totalAssets,
            ],
            'liabilities' => [
                'accounts' => $liabilities,
                'total' => $totalLiabilities,
            ],
            'equity' => [
                'accounts' => $equity,
                'total' => $totalEquity,
            ],
            'totals' => [
                'total_assets' => $totalAssets,
                'total_liabilities_and_equity' => $totalLiabilities + $totalEquity,
                'is_balanced' => abs($totalAssets - ($totalLiabilities + $totalEquity)) < 0.01,
            ],
            'period' => [
                'identifier' => $periodIdentifier ?? Carbon::now()->format('Y-m'),
            ],
        ];
    }

    /**
     * Get account balances grouped by parent accounts for a month
     */
    public function getGroupedBalances($periodIdentifier = null)
    {
        $periodIdentifier = $periodIdentifier ?? Carbon::now()->format('Y-m');

        $balances = AccountBalance::with(['account.parentAccount'])
            ->where('period_identifier', $periodIdentifier)
            ->get();

        $grouped = [];

        foreach ($balances as $balance) {
            $accountType = $balance->account->type;
            $parentName = $balance->account->parentAccount ?
                $balance->account->parentAccount->name :
                ucfirst($accountType) . ' Accounts';

            if (!isset($grouped[$accountType])) {
                $grouped[$accountType] = [];
            }

            if (!isset($grouped[$accountType][$parentName])) {
                $grouped[$accountType][$parentName] = [
                    'accounts' => [],
                    'total' => 0,
                ];
            }

            $normalBalance = $balance->getNormalBalance();

            $grouped[$accountType][$parentName]['accounts'][] = [
                'account_code' => $balance->account->code,
                'account_name' => $balance->account->name,
                'balance' => $normalBalance,
                'debit_balance' => $balance->debit_balance,
                'credit_balance' => $balance->credit_balance,
            ];

            $grouped[$accountType][$parentName]['total'] += $normalBalance;
        }

        return $grouped;
    }

    /**
     * Calculate retained earnings up to a specific date
     */
    public function calculateRetainedEarnings($date = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::now();

        // Get all revenue entries (including reversals)
        $revenueBalance = JournalEntry::whereHas('account', function ($query) {
                $query->where('type', 'revenue');
            })
            ->where('date', '<=', $date)
            ->sum(DB::raw('credit - debit'));

        // Get all expense entries (including COGS and reversals)
        $expenseBalance = JournalEntry::whereHas('account', function ($query) {
                $query->where('type', 'expense');
            })
            ->where('date', '<=', $date)
            ->sum(DB::raw('debit - credit'));

        // Retained Earnings = Revenue - Expenses
        return $revenueBalance - $expenseBalance;
    }

    /**
     * Calculate net income for the current period
     */
    public function calculateNetIncome($date = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::now();

        // Get all revenue entries for the period
        $revenueBalance = JournalEntry::whereHas('account', function ($query) {
                $query->where('type', 'revenue');
            })
            ->where('date', '<=', $date)
            ->sum(DB::raw('credit - debit'));

        // Get all expense entries (including COGS) for the period
        $expenseBalance = JournalEntry::whereHas('account', function ($query) {
                $query->where('type', 'expense');
            })
            ->where('date', '<=', $date)
            ->sum(DB::raw('debit - credit'));

        // Net Income = Revenue - Expenses
        return $revenueBalance - $expenseBalance;
    }

    /**
     * Get trial balance data for a month
     */
    public function getTrialBalance($periodIdentifier = null)
    {
        $periodIdentifier = $periodIdentifier ?? Carbon::now()->format('Y-m');

        $balances = AccountBalance::with('account')
            ->where('period_identifier', $periodIdentifier)
            ->get()
            ->map(function ($balance) {
                return [
                    'account_code' => $balance->account->code,
                    'account_name' => $balance->account->name,
                    'account_type' => $balance->account->type,
                    'debit_balance' => $balance->debit_balance,
                    'credit_balance' => $balance->credit_balance,
                    'normal_balance' => $balance->getNormalBalance(),
                ];
            });

        $totalDebits = $balances->sum('debit_balance');
        $totalCredits = $balances->sum('credit_balance');

        return [
            'balances' => $balances,
            'totals' => [
                'total_debits' => $totalDebits,
                'total_credits' => $totalCredits,
                'is_balanced' => abs($totalDebits - $totalCredits) < 0.01,
                'difference' => $totalDebits - $totalCredits,
            ],
            'period' => [
                'identifier' => $periodIdentifier,
            ],
        ];
    }

    /**
     * Rebuild all account balances for a specific month
     */
    public function rebuildBalances($periodIdentifier = null)
    {
        if ($periodIdentifier) {
            // Delete existing balances for the specific period
            AccountBalance::where('period_identifier', $periodIdentifier)
                ->delete();

            // Recreate balances for the specific period
            $date = Carbon::createFromFormat('Y-m', $periodIdentifier);
            return $this->calculateAndStoreBalances($date);
        } else {
            // Rebuild balances for current month
            return $this->calculateAndStoreBalances(Carbon::now());
        }
    }
}
