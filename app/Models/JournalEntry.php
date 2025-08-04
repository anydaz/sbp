<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    protected $fillable = [
        'date',
        'account_id',
        'debit',
        'credit',
        'reference_type',
        'reference_id',
        'description',
    ];
}
