<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'balance_date',
        'debit_balance',
        'credit_balance',
        'net_balance',
        'period_identifier',
    ];

    protected $casts = [
        'balance_date' => 'date',
        'debit_balance' => 'decimal:2',
        'credit_balance' => 'decimal:2',
        'net_balance' => 'decimal:2',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the normal balance for the account (debit or credit)
     */
    public function getNormalBalance()
    {
        $accountType = $this->account->type;

        // Assets, Expenses, and Dividends have normal debit balances
        if (in_array($accountType, ['asset', 'expense'])) {
            return $this->debit_balance - $this->credit_balance;
        }

        // Liabilities, Equity, and Revenue have normal credit balances
        if (in_array($accountType, ['liability', 'equity', 'revenue'])) {
            return $this->credit_balance - $this->debit_balance;
        }

        return $this->net_balance;
    }

    /**
     * Scope for getting balances by account type
     */
    public function scopeByAccountType($query, $accountType)
    {
        return $query->whereHas('account', function ($q) use ($accountType) {
            $q->where('type', $accountType);
        });
    }
}
