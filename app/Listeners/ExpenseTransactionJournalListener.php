<?php

namespace App\Listeners;

use App\Events\ExpenseTransactionCreated;
use App\Events\ExpenseTransactionUpdated;
use App\Events\ExpenseTransactionDeleted;
use App\Models\JournalEntry;
use App\Models\JournalBatch;
use App\Models\Account;

class ExpenseTransactionJournalListener
{
    public function handleCreated(ExpenseTransactionCreated $event)
    {
        $transaction = $event->transaction;

        $cashAccountId = Account::where('code', '1001')->first()->id; // Cash account

        $batch = JournalBatch::create([
            'date' => now(),
            'description' => 'Expense Transaction: ' . $transaction->notes,
            'reference_type' => 'ExpenseTransaction',
            'reference_id' => $transaction->id,
        ]);

        $batch->entries()->createMany([
            [
                'date' => now(),
                'account_id' => $transaction->account_id,  // Expense account
                'debit' => $transaction->amount,
                'reference_type' => 'ExpenseTransaction',
                'reference_id' => $transaction->id,
                'description' => 'Expense Transaction: ' . $transaction->notes,
                'credit' => 0
            ],
            [
                'date' => now(),
                'account_id' => $cashAccountId,  // Cash account
                'debit' => 0,
                'reference_type' => 'ExpenseTransaction',
                'reference_id' => $transaction->id,
                'description' => 'Expense Transaction: ' . $transaction->notes,
                'credit' => $transaction->amount
            ]
        ]);
    }

    public function handleUpdated(ExpenseTransactionUpdated $event)
    {
        $transaction = $event->transaction;

        // Delete old journal entries
        JournalBatch::where('reference_type', 'ExpenseTransaction')
            ->where('reference_id', $transaction->id)
            ->delete();

        // Create new ones
        $this->handleCreated(new ExpenseTransactionCreated($transaction));
    }

    public function handleDeleted(ExpenseTransactionDeleted $event)
    {
        $transaction = $event->transaction;

        JournalBatch::where('reference_type', 'ExpenseTransaction')
            ->where('reference_id', $transaction->id)
            ->delete();
    }

    public function subscribe($events)
    {
        $events->listen(
            ExpenseTransactionCreated::class,
            [ExpenseTransactionJournalListener::class, 'handleCreated']
        );

        $events->listen(
            ExpenseTransactionUpdated::class,
            [ExpenseTransactionJournalListener::class, 'handleUpdated']
        );

        $events->listen(
            ExpenseTransactionDeleted::class,
            [ExpenseTransactionJournalListener::class, 'handleDeleted']
        );
    }
}
