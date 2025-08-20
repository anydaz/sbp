<?php

namespace App\Listeners;

use App\Events\DeliveryNoteDeleted;
use App\Services\JournalService;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleDeliveryNoteJournalDeletion implements ShouldQueue
{
    public $tries = 3;

    protected $journalService;

    public function __construct(JournalService $journalService)
    {
        $this->journalService = $journalService;
    }

    public function handle(DeliveryNoteDeleted $event)
    {
        $deliveryNote = $event->deliveryNote;

        $reversalBatch = $this->journalService->reverseJournalEntries(
            'DeliveryNote',
            $deliveryNote->id,
            'Delivery Note deletion reversal #' . $deliveryNote->id
        );
    }

    public function failed(DeliveryNoteDeleted $event, $exception)
    {
        // Handle the failure
        echo "Failed to reverse journal entries for deleted Delivery Note ID: {$event->deliveryNote->id}. Error: {$exception->getMessage()}";
    }
}
