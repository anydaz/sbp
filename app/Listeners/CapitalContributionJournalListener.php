<?php

namespace App\Listeners;

use App\Events\CapitalContributionCreated;
use App\Events\CapitalContributionUpdated;
use App\Events\CapitalContributionDeleted;

use App\Services\JournalService;
use App\Models\Account;

class CapitalContributionJournalListener
{
    protected $journalService;

    public function __construct(JournalService $journalService)
    {
        $this->journalService = $journalService;
    }

    public function handleCreated(CapitalContributionCreated $event)
    {
        $contribution = $event->contribution;

        $cashAccountId = Account::where('code', '1001')->first()->id; // Cash account
        $equityAccountId = Account::where('code', '3001')->first()->id; // Owner's equity account

        $journalEntries = [
            [
                'date' => $contribution->date,
                'account_id' => $cashAccountId,
                'debit' => $contribution->amount,
                'credit' => 0,
                'reference_type' => 'CapitalContribution',
                'reference_id' => $contribution->id,
                'description' => 'Capital Contribution: ' . $contribution->notes,
            ],
            [
                'date' => $contribution->date,
                'account_id' => $equityAccountId,
                'debit' => 0,
                'credit' => $contribution->amount,
                'reference_type' => 'CapitalContribution',
                'reference_id' => $contribution->id,
                'description' => 'Capital Contribution: ' . $contribution->notes,
            ]
        ];

        // Create the journal batch with entries using the service
        $this->journalService->createJournalBatch([
            'date' => $contribution->date,
            'description' => 'Capital Contribution: ' . $contribution->notes,
            'reference_type' => 'CapitalContribution',
            'reference_id' => $contribution->id,
        ], $journalEntries);
    }

    public function handleUpdated(CapitalContributionUpdated $event)
    {
        $contribution = $event->contribution;

        // Reverse previous journal entries before creating new ones
        $this->journalService->reverseJournalEntries(
            'CapitalContribution',
            $contribution->id,
            'Capital Contribution update reversal: ' . $contribution->notes
        );

        // Create new journal entries with updated values
        $this->handleCreated(new CapitalContributionCreated($contribution));
    }

    public function handleDeleted(CapitalContributionDeleted $event)
    {
        $contribution = $event->contribution;

        // Reverse all journal entries for this capital contribution
        $this->journalService->reverseJournalEntries(
            'CapitalContribution',
            $contribution->id,
            'Capital Contribution deletion reversal: ' . $contribution->notes
        );
    }

    public function subscribe($events)
    {
        $events->listen(
            CapitalContributionCreated::class,
            [CapitalContributionJournalListener::class, 'handleCreated']
        );

        $events->listen(
            CapitalContributionUpdated::class,
            [CapitalContributionJournalListener::class, 'handleUpdated']
        );

        $events->listen(
            CapitalContributionDeleted::class,
            [CapitalContributionJournalListener::class, 'handleDeleted']
        );
    }
}
