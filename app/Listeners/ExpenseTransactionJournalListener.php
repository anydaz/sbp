<?php

namespace App\Listeners;

use App\Events\ExpenseTransactionCreated;
use App\Events\ExpenseTransactionUpdated;
use App\Events\ExpenseTransactionDeleted;
use App\Services\JournalService;
use App\Models\Account;

class ExpenseTransactionJournalListener
{
    protected $journalService;

    public function __construct(JournalService $journalService)
    {
        $this->journalService = $journalService;
    }

    public function handleCreated(ExpenseTransactionCreated $event)
    {
        $transaction = $event->transaction;

        $cashAccountId = Account::where('code', '1001')->first()->id; // Cash account

        // Prepare journal entries for expense transaction creation
        $journalEntries = [
            [
                'date' => now(),
                'account_id' => $transaction->account_id,  // Expense account
                'debit' => $transaction->amount,
                'credit' => 0,
                'reference_type' => 'ExpenseTransaction',
                'reference_id' => $transaction->id,
                'description' => 'Expense Transaction: ' . $transaction->notes,
            ],
            [
                'date' => now(),
                'account_id' => $cashAccountId,  // Cash account
                'debit' => 0,
                'credit' => $transaction->amount,
                'reference_type' => 'ExpenseTransaction',
                'reference_id' => $transaction->id,
                'description' => 'Expense Transaction: ' . $transaction->notes,
            ]
        ];

        // Create the journal batch with entries using the service
        $this->journalService->createJournalBatch([
            'date' => now(),
            'description' => 'Expense Transaction: ' . $transaction->notes,
            'reference_type' => 'ExpenseTransaction',
            'reference_id' => $transaction->id,
        ], $journalEntries);
    }

    public function handleUpdated(ExpenseTransactionUpdated $event)
    {
        $transaction = $event->transaction;

        // Reverse the latest journal batch for this expense transaction
        $this->journalService->reverseJournalEntries(
            'ExpenseTransaction',
            $transaction->id,
            'Expense Transaction update reversal: ' . $transaction->notes
        );

        // Create new entries based on the updated expense transaction
        $this->handleCreated(new ExpenseTransactionCreated($transaction));
    }

    public function handleDeleted(ExpenseTransactionDeleted $event)
    {
        $transaction = $event->transaction;

        // Reverse the latest journal batch for this expense transaction
        $this->journalService->reverseJournalEntries(
            'ExpenseTransaction',
            $transaction->id,
            'Expense Transaction deletion reversal: ' . $transaction->notes
        );
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
