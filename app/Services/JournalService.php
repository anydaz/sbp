<?php

namespace App\Services;

use App\Models\JournalBatch;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class JournalService
{
    public function reverseJournalEntries($referenceType, $referenceId, $description = null)
    {
        // Find the latest non-reversal batch for the given reference
        $latestBatch = $this->getLatestJournalBatch($referenceType, $referenceId);

        if (!$latestBatch) {
            return null;
        }

        // Create a single reversal batch for the latest batch entries
        $reversalBatch = DB::transaction(function () use ($latestBatch, $referenceType, $referenceId, $description) {
            $reversalBatch = JournalBatch::create([
                'date' => now(),
                'description' => $description ?: "Reversal for {$referenceType} #{$referenceId}",
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'is_reversal_entries' => true,
                'reversal_reference_id' => $latestBatch->id,
            ]);

            $reversalEntries = [];

            // Create reversal entries for the latest batch
            foreach ($latestBatch->entries as $entry) {
                $reversalEntries[] = [
                    'account_id' => $entry->account_id,
                    'debit' => $entry->credit, // Swap debit and credit to reverse
                    'credit' => $entry->debit,
                    'reference_type' => $referenceType,
                    'reference_id' => $referenceId,
                    'description' => "Reversal: " . $entry->description,
                    'date' => now(),
                ];
            }

            // Create all reversal entries
            if (!empty($reversalEntries)) {
                $reversalBatch->entries()->createMany($reversalEntries);
            }

            return $reversalBatch;
        });

        return $reversalBatch;
    }

    public function getLatestJournalBatch($referenceType, $referenceId)
    {
        return JournalBatch::where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->where('is_reversal_entries', false)
            ->with('entries.account')
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function createJournalBatch($batchData, $entries)
    {
        return DB::transaction(function () use ($batchData, $entries) {
            // Ensure is_reversal_entries is set to false for normal entries
            $batchData['is_reversal_entries'] = $batchData['is_reversal_entries'] ?? false;

            $batch = JournalBatch::create($batchData);

            if (!empty($entries)) {
                $batch->entries()->createMany($entries);
            }

            return $batch;
        });
    }
}
