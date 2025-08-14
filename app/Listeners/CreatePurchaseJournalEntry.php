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
        $accountPayableAccountId = Account::where('code', '2001')->first()->id; // Accounts Payable
        $inventoryInTransitAccountId = Account::where('code', '1005')->first()->id; // Inventory in Transit
        
        // Determine payment type based on payment_category_id (1 = Cash, 2 = Credit)
        $paymentType = $purchaseOrder->payment_category_id == 1 ? 'cash' : 'credit';

        DB::transaction(function () use ($purchaseOrder, $cashAccountId, $accountPayableAccountId, $inventoryInTransitAccountId, $paymentType) {
            $batch = JournalBatch::create([
                'date' => $purchaseOrder->date,
                'description' => 'Purchase transaction #' . $purchaseOrder->purchase_number,
                'reference_type' => 'PurchaseOrder',
                'reference_id' => $purchaseOrder->id,
                'date' => now()
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
                    'account_id' => $paymentType == 'cash' ? $cashAccountId : $accountPayableAccountId,
                    'debit' => 0,
                    'credit' => $purchaseOrder->total,
                    'reference_type' => 'PurchaseOrder',
                    'reference_id' => $purchaseOrder->id,
                    'description' => $paymentType == 'cash' ? 'Cash paid for purchase' : 'Credit purchase - accounts payable',
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


