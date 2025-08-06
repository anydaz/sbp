<?php

namespace App\Listeners;

use App\Events\DeliveryNoteCreated;
use App\Models\Account;
use App\Models\JournalBatch;
use Illuminate\Support\Facades\DB;

use Illuminate\Contracts\Queue\ShouldQueue;

class CreateDeliveryNoteJournalEntry implements ShouldQueue
{
    public $tries = 3;
    public function handle(DeliveryNoteCreated $event)
    {
        $deliveryNote = $event->deliveryNote;
        $inventoryInTransitAccountId = Account::where('code', '1005')->first()->id; // Inventory in Transit
        $inventoryAccountId = Account::where('code', '1002')->first()->id; // Inventory

        DB::transaction(function () use ($deliveryNote, $inventoryAccountId, $inventoryInTransitAccountId) {
            $batch = JournalBatch::create([
                'date' => now(),
                'description' => 'Delivery Note transaction #' . $deliveryNote->id,
                'reference_type' => 'DeliveryNote',
                'reference_id' => $deliveryNote->id,
            ]);

            $batch->entries()->createMany([
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
            ]);
        });
    }

    public function failed(DeliveryNoteCreated $event, $exception)
    {
        // Handle the failure, e.g., log the error or notify the admin
        echo "Failed to create journal entry for Delivery Note ID: {$event->deliveryNote->id}. Error: {$exception->getMessage()}";
    }
}


