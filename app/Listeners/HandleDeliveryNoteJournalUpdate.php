<?php

namespace App\Listeners;

use App\Events\DeliveryNoteUpdated;
use App\Models\Account;
use App\Services\JournalService;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleDeliveryNoteJournalUpdate implements ShouldQueue
{
    public $tries = 3;

    protected $journalService;

    public function __construct(JournalService $journalService)
    {
        $this->journalService = $journalService;
    }

    public function handle(DeliveryNoteUpdated $event)
    {
        $deliveryNote = $event->deliveryNote;
        $originalDeliveryNote = $event->originalDeliveryNote;
        $inventoryInTransitAccountId = Account::where('code', '1005')->first()->id; // Inventory in Transit
        $inventoryAccountId = Account::where('code', '1004')->first()->id; // Inventory

        DB::transaction(function () use ($deliveryNote, $inventoryAccountId, $inventoryInTransitAccountId) {
            // Reverse the latest journal batch for this delivery note
            $this->journalService->reverseJournalEntries(
                'DeliveryNote',
                $deliveryNote->id,
                'Delivery Note update reversal #' . $deliveryNote->id
            );

            // Create new entries based on the updated delivery note
            $journalEntries = [
                [
                    'account_id' => $inventoryAccountId,
                    'debit' => $deliveryNote->total,
                    'credit' => 0,
                    'reference_type' => 'DeliveryNote',
                    'reference_id' => $deliveryNote->id,
                    'description' => 'Updated inventory received for delivery',
                    'date' => now(),
                ],
                [
                    'account_id' => $inventoryInTransitAccountId,
                    'debit' => 0,
                    'credit' => $deliveryNote->total,
                    'reference_type' => 'DeliveryNote',
                    'reference_id' => $deliveryNote->id,
                    'description' => 'Updated inventory in transit for delivery',
                    'date' => now(),
                ]
            ];

            // Create the new journal batch with entries
            $this->journalService->createJournalBatch([
                'date' => now(),
                'description' => 'Delivery Note updated transaction #' . $deliveryNote->id,
                'reference_type' => 'DeliveryNote',
                'reference_id' => $deliveryNote->id,
            ], $journalEntries);
        });
    }

    public function failed(DeliveryNoteUpdated $event, $exception)
    {
        // Handle the failure
        echo "Failed to update journal entries for Delivery Note ID: {$event->deliveryNote->id}. Error: {$exception->getMessage()}";
    }
}
