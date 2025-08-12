<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseTransaction extends Model
{
    protected $fillable = [
        'date',
        'amount',
        'notes',
        'account_id'
    ];

    protected $attributes = ['state' => "active"];

    protected $casts = [
        'date' => 'date',
        'amount' => 'float'
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
