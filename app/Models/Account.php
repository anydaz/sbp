<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = [
        'name',
        'type',
        'parent_account_id',
    ];

    public function parentAccount()
    {
        return $this->belongsTo(Account::class, 'parent_account_id');
    }
}
