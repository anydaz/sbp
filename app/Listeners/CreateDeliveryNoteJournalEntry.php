<?php

namespace App\Listeners;

use App\Events\DeliveryNoteCreated;
use App\Models\Account;
use App\Services\JournalService;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreateDeliveryNoteJournalEntry implements ShouldQueue
{
    public $tries = 3;

    protected $journalService;

    public function __construct(JournalService $journalService)
    {
        $this->journalService = $journalService;
    }

    public function handle(DeliveryNoteCreated $event)
    {
        $deliveryNote = $event->deliveryNote;
        $inventoryInTransitAccountId = Account::where('code', '1005')->first()->id; // Inventory in Transit
        $inventoryAccountId = Account::where('code', '1004')->first()->id; // Inventory

        // Prepare journal entries for delivery note creation
        $journalEntries = [
            [
                'account_id' => $inventoryAccountId,
                'debit' => $deliveryNote->total,
                'credit' => 0,
                'reference_type' => 'DeliveryNote',
                'reference_id' => $deliveryNote->id,
                'description' => 'Inventory received for delivery',
                'date' => now(),
            ],
            [
                'account_id' => $inventoryInTransitAccountId,
                'debit' => 0,
                'credit' => $deliveryNote->total,
                'reference_type' => 'DeliveryNote',
                'reference_id' => $deliveryNote->id,
                'description' => 'Inventory in transit for delivery',
                'date' => now(),
            ]
        ];

        // Create the journal batch with entries using the service
        $this->journalService->createJournalBatch([
            'date' => now(),
            'description' => 'Delivery Note transaction #' . $deliveryNote->id,
            'reference_type' => 'DeliveryNote',
            'reference_id' => $deliveryNote->id,
        ], $journalEntries);
    }

    public function failed(DeliveryNoteCreated $event, $exception)
    {
        // Handle the failure, e.g., log the error or notify the admin
        echo "Failed to create journal entry for Delivery Note ID: {$event->deliveryNote->id}. Error: {$exception->getMessage()}";
    }
}


