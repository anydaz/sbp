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
        $accountPayableAccountId = Account::where('code', '2001')->first()->id; // Accounts Payable
        $inventoryInTransitAccountId = Account::where('code', '1005')->first()->id; // Inventory in Transit

        // Determine payment types for both original and new purchase orders
        $originalPaymentType = $originalPurchaseOrder->payment_category_id == 1 ? 'cash' : 'credit';
        $newPaymentType = $purchaseOrder->payment_category_id == 1 ? 'cash' : 'credit';

        DB::transaction(function () use ($purchaseOrder, $originalPurchaseOrder, $cashAccountId, $accountPayableAccountId, $inventoryInTransitAccountId, $originalPaymentType, $newPaymentType) {
            // First create reversal entries for the original purchase order
            $reversalBatch = JournalBatch::create([
                'date' => now(),
                'description' => 'Purchase reversal for update #' . $purchaseOrder->purchase_number,
                'reference_type' => 'PurchaseOrder',
                'reference_id' => $purchaseOrder->id,
            ]);

            $reversalEntries = [
                // Reverse original inventory in transit (credit to reverse the original debit)
                [
                    'account_id' => $inventoryInTransitAccountId,
                    'debit' => 0,
                    'credit' => $originalPurchaseOrder->total,
                    'reference_type' => 'PurchaseOrder',
                    'reference_id' => $purchaseOrder->id,
                    'description' => 'Reverse inventory in transit for updated purchase',
                    'date' => now(),
                ]
            ];

            if ($originalPaymentType == 'cash') {
                // Reverse original cash payment (debit to reverse the original credit)
                $reversalEntries[] = [
                    'account_id' => $cashAccountId,
                    'debit' => $originalPurchaseOrder->total,
                    'credit' => 0,
                    'reference_type' => 'PurchaseOrder',
                    'reference_id' => $purchaseOrder->id,
                    'description' => 'Reverse cash paid for updated purchase',
                    'date' => now(),
                ];
            } else {
                // For original credit purchases, reverse based on down payment
                if ($originalPurchaseOrder->down_payment > 0) {
                    // Reverse original down payment
                    $reversalEntries[] = [
                        'account_id' => $cashAccountId,
                        'debit' => $originalPurchaseOrder->down_payment,
                        'credit' => 0,
                        'reference_type' => 'PurchaseOrder',
                        'reference_id' => $purchaseOrder->id,
                        'description' => 'Reverse cash down payment for updated purchase',
                        'date' => now(),
                    ];

                    // Reverse original remaining accounts payable
                    $originalRemainingBalance = $originalPurchaseOrder->total - $originalPurchaseOrder->down_payment;
                    if ($originalRemainingBalance > 0) {
                        $reversalEntries[] = [
                            'account_id' => $accountPayableAccountId,
                            'debit' => $originalRemainingBalance,
                            'credit' => 0,
                            'reference_type' => 'PurchaseOrder',
                            'reference_id' => $purchaseOrder->id,
                            'description' => 'Reverse remaining balance payable for updated purchase',
                            'date' => now(),
                        ];
                    }
                } else {
                    // Reverse original full accounts payable
                    $reversalEntries[] = [
                        'account_id' => $accountPayableAccountId,
                        'debit' => $originalPurchaseOrder->total,
                        'credit' => 0,
                        'reference_type' => 'PurchaseOrder',
                        'reference_id' => $purchaseOrder->id,
                        'description' => 'Reverse accounts payable for updated purchase',
                        'date' => now(),
                    ];
                }
            }

            $reversalBatch->entries()->createMany($reversalEntries);

            // Then create new entries based on the updated purchase order
            $batch = JournalBatch::create([
                'date' => $purchaseOrder->date,
                'description' => 'Updated purchase transaction #' . $purchaseOrder->purchase_number,
                'reference_type' => 'PurchaseOrder',
                'reference_id' => $purchaseOrder->id,
            ]);

            $journalEntries = [
                [
                    'account_id' => $inventoryInTransitAccountId,
                    'debit' => $purchaseOrder->total,
                    'credit' => 0,
                    'reference_type' => 'PurchaseOrder',
                    'reference_id' => $purchaseOrder->id,
                    'description' => 'Updated inventory in transit for purchase',
                    'date' => now(),
                ]
            ];

            if ($newPaymentType == 'cash') {
                // For cash purchases, full amount should be paid immediately
                $journalEntries[] = [
                    'account_id' => $cashAccountId,
                    'debit' => 0,
                    'credit' => $purchaseOrder->total,
                    'reference_type' => 'PurchaseOrder',
                    'reference_id' => $purchaseOrder->id,
                    'description' => 'Updated cash paid for purchase',
                    'date' => now(),
                ];
            } else {
                // For credit purchases with down payment
                if ($purchaseOrder->down_payment > 0) {
                    // Down payment in cash
                    $journalEntries[] = [
                        'account_id' => $cashAccountId,
                        'debit' => 0,
                        'credit' => $purchaseOrder->down_payment,
                        'reference_type' => 'PurchaseOrder',
                        'reference_id' => $purchaseOrder->id,
                        'description' => 'Updated cash down payment for credit purchase',
                        'date' => now(),
                    ];

                    // Remaining balance as accounts payable
                    $remainingBalance = $purchaseOrder->total - $purchaseOrder->down_payment;
                    if ($remainingBalance > 0) {
                        $journalEntries[] = [
                            'account_id' => $accountPayableAccountId,
                            'debit' => 0,
                            'credit' => $remainingBalance,
                            'reference_type' => 'PurchaseOrder',
                            'reference_id' => $purchaseOrder->id,
                            'description' => 'Updated remaining balance payable for credit purchase',
                            'date' => now(),
                        ];
                    }
                } else {
                    // Full credit purchase - no down payment
                    $journalEntries[] = [
                        'account_id' => $accountPayableAccountId,
                        'debit' => 0,
                        'credit' => $purchaseOrder->total,
                        'reference_type' => 'PurchaseOrder',
                        'reference_id' => $purchaseOrder->id,
                        'description' => 'Updated credit purchase - accounts payable',
                        'date' => now(),
                    ];
                }
            }

            $batch->entries()->createMany($journalEntries);
        });
    }

    public function failed(PurchaseOrderUpdated $event, $exception)
    {
        echo "Failed to update journal entries for Purchase Order ID: {$event->purchaseOrder->id}. Error: {$exception->getMessage()}";
    }
}
