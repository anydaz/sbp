<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalBatch extends Model
{
    protected $fillable = [
        'date',
        'description',
        'reference_type',
        'reference_id',
    ];

    public function entries()
    {
        return $this->hasMany(JournalEntry::class, 'journal_batch_id');
    }
}
