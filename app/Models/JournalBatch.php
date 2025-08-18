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
        'is_reversal_entries',
        'reversal_reference_id',
    ];

    protected $casts = [
        'is_reversal_entries' => 'boolean',
        'date' => 'date',
    ];

    public function entries()
    {
        return $this->hasMany(JournalEntry::class, 'journal_batch_id');
    }

    /**
     * Get the original batch that this reversal batch references
     */
    public function originalBatch()
    {
        return $this->belongsTo(JournalBatch::class, 'reversal_reference_id');
    }

    /**
     * Get all reversal batches that reference this batch
     */
    public function reversalBatches()
    {
        return $this->hasMany(JournalBatch::class, 'reversal_reference_id');
    }

    /**
     * Check if this batch is a reversal batch
     */
    public function isReversal()
    {
        return $this->is_reversal_entries;
    }

    /**
     * Check if this batch has been reversed
     */
    public function hasBeenReversed()
    {
        return $this->reversalBatches()->exists();
    }
}
