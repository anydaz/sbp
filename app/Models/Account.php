<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = [
        'code',
        'name',
        'type',
        'parent_account_id',
    ];

    public function parentAccount()
    {
        return $this->belongsTo(Account::class, 'parent_account_id');
    }

    public function childAccounts()
    {
        return $this->hasMany(Account::class, 'parent_account_id');
    }

    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class);
    }

    public function accountBalances()
    {
        return $this->hasMany(AccountBalance::class);
    }

    /**
     * Check if this account has a normal debit balance
     */
    public function hasNormalDebitBalance()
    {
        return in_array($this->type, ['asset', 'expense']);
    }

    /**
     * Check if this account has a normal credit balance
     */
    public function hasNormalCreditBalance()
    {
        return in_array($this->type, ['liability', 'equity', 'revenue']);
    }
}
