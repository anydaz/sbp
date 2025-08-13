<?php

namespace App\Listeners;

use App\Events\CapitalContributionCreated;
use App\Events\CapitalContributionUpdated;
use App\Events\CapitalContributionDeleted;

use App\Models\JournalEntry;
use App\Models\JournalBatch;
use App\Models\Account;

class CapitalContributionJournalListener
{
    public function handleCreated(CapitalContributionCreated $event)
    {
        $contribution = $event->contribution;

        $cashAccountId = Account::where('code', '1001')->first()->id; // Assuming '1001' is the cash account code
        $equityAccountId = Account::where('code', '3001')->first()->id; // Assuming '3001' is the owner's equity account code

        $batch = JournalBatch::create([
            'date' => now(),
            'description' => 'Capital Contribution: ' . $contribution->notes,
            'reference_type' => 'CapitalContribution',
            'reference_id' => $contribution->id,
        ]);

        $batch->entries()->createMany([
            [
                'date' => now(),
                'account_id' => $cashAccountId,
                'debit' => $contribution->amount,
                'reference_type' => 'CapitalContribution',
                'reference_id' => $contribution->id,
                'description' => 'Capital Contribution: ' . $contribution->notes,
                'credit' => 0
            ],
            [
                'date' => now(),
                'account_id' => $equityAccountId,
                'debit' => 0,
                'reference_type' => 'CapitalContribution',
                'reference_id' => $contribution->id,
                'description' => 'Capital Contribution: ' . $contribution->notes,
                'credit' => $contribution->amount
            ]
        ]);

    }

    public function handleUpdated(CapitalContributionUpdated $event)
    {

    }

    public function handleDeleted(CapitalContributionDeleted $event)
    {

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
