<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    protected $fillable = [
        'date',
        'account_id',
        'journal_batch_id',
        'debit',
        'credit',
        'reference_type',
        'reference_id',
        'description',
    ];

    protected $casts = [
        'debit' => 'float',
		'credit' => 'float',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function batch()
    {
        return $this->belongsTo(JournalBatch::class, 'journal_batch_id');
    }
}
