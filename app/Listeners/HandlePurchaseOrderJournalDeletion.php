<?php

namespace App\Listeners;

use App\Events\PurchaseOrderDeleted;
use App\Models\Account;
use App\Models\JournalBatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandlePurchaseOrderJournalDeletion implements ShouldQueue
{
    public $tries = 3;

    public function handle(PurchaseOrderDeleted $event)
    {
        $purchaseOrder = $event->purchaseOrder;
        $cashAccountId = Account::where('code', '1001')->first()->id; // Cash
        $accountPayableAccountId = Account::where('code', '2001')->first()->id; // Accounts Payable
        $inventoryInTransitAccountId = Account::where('code', '1005')->first()->id; // Inventory in Transit
        
        // Determine payment type based on payment_category_id (1 = Cash, 2 = Credit)
        $paymentType = $purchaseOrder->payment_category_id == 1 ? 'cash' : 'credit';

        DB::transaction(function () use ($purchaseOrder, $cashAccountId, $accountPayableAccountId, $inventoryInTransitAccountId, $paymentType) {
            $batch = JournalBatch::create([
                'date' => now(),
                'description' => 'Purchase deletion reversal #' . $purchaseOrder->purchase_number,
                'reference_type' => 'PurchaseOrder',
                'reference_id' => $purchaseOrder->id,
            ]);

            $batch->entries()->createMany([
                [
                    'account_id' => $inventoryInTransitAccountId,
                    'debit' => 0,
                    'credit' => $purchaseOrder->total,
                    'reference_type' => 'PurchaseOrder',
                    'reference_id' => $purchaseOrder->id,
                    'description' => 'Reverse inventory in transit for deleted purchase',
                    'date' => now(),
                ],
                [
                    'account_id' => $paymentType == 'cash' ? $cashAccountId : $accountPayableAccountId,
                    'debit' => $purchaseOrder->total,
                    'credit' => 0,
                    'reference_type' => 'PurchaseOrder',
                    'reference_id' => $purchaseOrder->id,
                    'description' => $paymentType == 'cash' ? 'Reverse cash paid for deleted purchase' : 'Reverse accounts payable for deleted purchase',
                    'date' => now(),
                ]
            ]);
        });
    }

    public function failed(PurchaseOrderDeleted $event, $exception)
    {
        echo "Failed to reverse journal entries for deleted Purchase Order ID: {$event->purchaseOrder->id}. Error: {$exception->getMessage()}";
    }
}
