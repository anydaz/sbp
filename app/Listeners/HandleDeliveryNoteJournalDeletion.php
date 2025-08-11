<?php

namespace App\Listeners;

use App\Events\DeliveryNoteDeleted;
use App\Models\Account;
use App\Models\JournalBatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleDeliveryNoteJournalDeletion implements ShouldQueue
{
    public $tries = 3;

    public function handle(DeliveryNoteDeleted $event)
    {
        $deliveryNote = $event->deliveryNote;
        $inventoryInTransitAccountId = Account::where('code', '1005')->first()->id; // Inventory in Transit
        $inventoryAccountId = Account::where('code', '1004')->first()->id; // Inventory

        DB::transaction(function () use ($deliveryNote, $inventoryAccountId, $inventoryInTransitAccountId) {
            $batch = JournalBatch::create([
                'date' => now(),
                'description' => 'Delivery Note deletion reversal #' . $deliveryNote->id,
                'reference_type' => 'DeliveryNote',
                'reference_id' => $deliveryNote->id,
            ]);

            $batch->entries()->createMany([
                [
                    'account_id' => $inventoryAccountId,
                    'debit' => 0,
                    'credit' => $deliveryNote->total,
                    'reference_type' => 'DeliveryNote',
                    'reference_id' => $deliveryNote->id,
                    'description' => 'Reverse inventory for deleted delivery note',
                    'date' => now(),
                ],
                [
                    'account_id' => $inventoryInTransitAccountId,
                    'debit' => $deliveryNote->total,
                    'credit' => 0,
                    'reference_type' => 'DeliveryNote',
                    'reference_id' => $deliveryNote->id,
                    'description' => 'Reverse inventory in transit for deleted delivery note',
                    'date' => now(),
                ]
            ]);
        });
    }

    public function failed(DeliveryNoteDeleted $event, $exception)
    {
        // Handle the failure
        echo "Failed to reverse journal entries for deleted Delivery Note ID: {$event->deliveryNote->id}. Error: {$exception->getMessage()}";
    }
}
