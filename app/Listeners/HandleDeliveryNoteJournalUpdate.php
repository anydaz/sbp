<?php

namespace App\Listeners;

use App\Events\DeliveryNoteUpdated;
use App\Models\Account;
use App\Models\JournalBatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleDeliveryNoteJournalUpdate implements ShouldQueue
{
    public $tries = 3;

    public function handle(DeliveryNoteUpdated $event)
    {
        $deliveryNote = $event->deliveryNote;
        $originalDeliveryNote = $event->originalDeliveryNote;
        $inventoryInTransitAccountId = Account::where('code', '1005')->first()->id; // Inventory in Transit
        $inventoryAccountId = Account::where('code', '1004')->first()->id; // Inventory

        DB::transaction(function () use ($deliveryNote, $originalDeliveryNote, $inventoryAccountId, $inventoryInTransitAccountId) {
            // First create reversal entry for the original amounts
            $batch = JournalBatch::create([
                'date' => now(),
                'description' => 'Delivery Note update reversal #' . $deliveryNote->id,
                'reference_type' => 'DeliveryNote',
                'reference_id' => $deliveryNote->id,
            ]);

            $batch->entries()->createMany([
                [
                    'account_id' => $inventoryAccountId,
                    'debit' => 0,
                    'credit' => $originalDeliveryNote->total,
                    'reference_type' => 'DeliveryNote',
                    'reference_id' => $deliveryNote->id,
                    'description' => 'Reverse inventory for delivery note update',
                    'date' => now(),
                ],
                [
                    'account_id' => $inventoryInTransitAccountId,
                    'debit' => $originalDeliveryNote->total,
                    'credit' => 0,
                    'reference_type' => 'DeliveryNote',
                    'reference_id' => $deliveryNote->id,
                    'description' => 'Reverse inventory in transit for delivery note update',
                    'date' => now(),
                ]
            ]);

            // Then create new entry for the updated amounts
            $newBatch = JournalBatch::create([
                'date' => now(),
                'description' => 'Delivery Note updated transaction #' . $deliveryNote->id,
                'reference_type' => 'DeliveryNote',
                'reference_id' => $deliveryNote->id,
            ]);

            $newBatch->entries()->createMany([
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
            ]);
        });
    }

    public function failed(DeliveryNoteUpdated $event, $exception)
    {
        // Handle the failure
        echo "Failed to update journal entries for Delivery Note ID: {$event->deliveryNote->id}. Error: {$exception->getMessage()}";
    }
}
