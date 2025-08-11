<?php

namespace App\Listeners;

use App\Events\PurchaseOrderUpdated;
use App\Models\Account;
use App\Models\JournalBatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandlePurchaseOrderJournalUpdate implements ShouldQueue
{
    public $tries = 3;

    public function handle(PurchaseOrderUpdated $event)
    {
        $purchaseOrder = $event->purchaseOrder;
        $originalPurchaseOrder = $event->originalPurchaseOrder;
        $cashAccountId = Account::where('code', '1001')->first()->id; // Cash
        $inventoryInTransitAccountId = Account::where('code', '1005')->first()->id; // Inventory in Transit

        DB::transaction(function () use ($purchaseOrder, $originalPurchaseOrder, $cashAccountId, $inventoryInTransitAccountId) {
            // First create reversal entries for the original amounts
            $reversalBatch = JournalBatch::create([
                'date' => now(),
                'description' => 'Purchase reversal for update #' . $purchaseOrder->purchase_number,
                'reference_type' => 'PurchaseOrder',
                'reference_id' => $purchaseOrder->id,
            ]);

            $reversalBatch->entries()->createMany([
                [
                    'account_id' => $inventoryInTransitAccountId,
                    'debit' => 0,
                    'credit' => $originalPurchaseOrder->total,
                    'reference_type' => 'PurchaseOrder',
                    'reference_id' => $purchaseOrder->id,
                    'description' => 'Reverse inventory in transit for updated purchase',
                    'date' => now(),
                ],
                [
                    'account_id' => $cashAccountId,
                    'debit' => $originalPurchaseOrder->total,
                    'credit' => 0,
                    'reference_type' => 'PurchaseOrder',
                    'reference_id' => $purchaseOrder->id,
                    'description' => 'Reverse cash paid for updated purchase',
                    'date' => now(),
                ]
            ]);

            // Then create new entries for the updated amounts
            $newBatch = JournalBatch::create([
                'date' => now(),
                'description' => 'Updated purchase transaction #' . $purchaseOrder->purchase_number,
                'reference_type' => 'PurchaseOrder',
                'reference_id' => $purchaseOrder->id,
            ]);

            $newBatch->entries()->createMany([
                [
                    'account_id' => $inventoryInTransitAccountId,
                    'debit' => $purchaseOrder->total,
                    'credit' => 0,
                    'reference_type' => 'PurchaseOrder',
                    'reference_id' => $purchaseOrder->id,
                    'description' => 'Updated inventory in transit for purchase',
                    'date' => now(),
                ],
                [
                    'account_id' => $cashAccountId,
                    'debit' => 0,
                    'credit' => $purchaseOrder->total,
                    'reference_type' => 'PurchaseOrder',
                    'reference_id' => $purchaseOrder->id,
                    'description' => 'Updated cash paid for purchase',
                    'date' => now(),
                ]
            ]);
        });
    }

    public function failed(PurchaseOrderUpdated $event, $exception)
    {
        echo "Failed to update journal entries for Purchase Order ID: {$event->purchaseOrder->id}. Error: {$exception->getMessage()}";
    }
}
