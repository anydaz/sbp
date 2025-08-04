<?php

namespace App\Listeners;

use App\Events\PurchaseOrderCreated;
use App\Models\Account;
use App\Models\JournalBatch;
use Illuminate\Support\Facades\DB;

use Illuminate\Contracts\Queue\ShouldQueue;

class CreatePurchaseJournalEntry implements ShouldQueue
{
    public $tries = 3;
    public function handle(PurchaseOrderCreated $event)
    {
        $purchaseOrder = $event->purchaseOrder;
        $cashAccountId = Account::where('code', '1001')->first()->id; // Cash
        $inventoryInTransitAccountId = Account::where('code', '1005')->first()->id; // Inventory in Transit

        DB::transaction(function () use ($purchaseOrder, $cashAccountId, $inventoryInTransitAccountId) {
            $batch = JournalBatch::create([
                'date' => now(),
                'description' => 'Purchase transaction #' . $purchaseOrder->purchase_number,
                'reference_type' => 'PurchaseOrder',
                'reference_id' => $purchaseOrder->id,
            ]);

            $batch->entries()->createMany([
                [
                    'account_id' => $inventoryInTransitAccountId,
                    'debit' => $purchaseOrder->total,
                    'credit' => 0,
                    'reference_type' => 'PurchaseOrder',
                    'reference_id' => $purchaseOrder->id,
                    'description' => 'Inventory in transit for purchase',
                    'date' => now(),
                ],
                [
                    'account_id' => $cashAccountId,
                    'debit' => 0,
                    'credit' => $purchaseOrder->total,
                    'reference_type' => 'PurchaseOrder',
                    'reference_id' => $purchaseOrder->id,
                    'description' => 'Cash paid for purchase',
                    'date' => now(),
                ]
            ]);
        });
    }

    public function failed(PurchaseOrderCreated $event, $exception)
    {
        // Handle the failure, e.g., log the error or notify the admin
        echo "Failed to create journal entry for Purchase Order ID: {$event->purchaseOrder->id}. Error: {$exception->getMessage()}";
    }
}


