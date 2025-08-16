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
            // Create reversal entries for the deleted purchase order
            $batch = JournalBatch::create([
                'date' => now(),
                'description' => 'Purchase deletion reversal #' . $purchaseOrder->purchase_number,
                'reference_type' => 'PurchaseOrder',
                'reference_id' => $purchaseOrder->id,
            ]);

            $journalEntries = [
                // Reverse inventory in transit (credit to reverse the original debit)
                [
                    'account_id' => $inventoryInTransitAccountId,
                    'debit' => 0,
                    'credit' => $purchaseOrder->total,
                    'reference_type' => 'PurchaseOrder',
                    'reference_id' => $purchaseOrder->id,
                    'description' => 'Reverse inventory in transit for deleted purchase',
                    'date' => now(),
                ]
            ];

            if ($paymentType == 'cash') {
                // Reverse cash payment (debit to reverse the original credit)
                $journalEntries[] = [
                    'account_id' => $cashAccountId,
                    'debit' => $purchaseOrder->total,
                    'credit' => 0,
                    'reference_type' => 'PurchaseOrder',
                    'reference_id' => $purchaseOrder->id,
                    'description' => 'Reverse cash paid for deleted purchase',
                    'date' => now(),
                ];
            } else {
                // For credit purchases, reverse the entries based on down payment
                if ($purchaseOrder->down_payment > 0) {
                    // Reverse down payment (debit cash to reverse the original credit)
                    $journalEntries[] = [
                        'account_id' => $cashAccountId,
                        'debit' => $purchaseOrder->down_payment,
                        'credit' => 0,
                        'reference_type' => 'PurchaseOrder',
                        'reference_id' => $purchaseOrder->id,
                        'description' => 'Reverse cash down payment for deleted credit purchase',
                        'date' => now(),
                    ];

                    // Reverse remaining accounts payable (debit to reverse the original credit)
                    $remainingBalance = $purchaseOrder->total - $purchaseOrder->down_payment;
                    if ($remainingBalance > 0) {
                        $journalEntries[] = [
                            'account_id' => $accountPayableAccountId,
                            'debit' => $remainingBalance,
                            'credit' => 0,
                            'reference_type' => 'PurchaseOrder',
                            'reference_id' => $purchaseOrder->id,
                            'description' => 'Reverse remaining balance payable for deleted credit purchase',
                            'date' => now(),
                        ];
                    }
                } else {
                    // Reverse full accounts payable (debit to reverse the original credit)
                    $journalEntries[] = [
                        'account_id' => $accountPayableAccountId,
                        'debit' => $purchaseOrder->total,
                        'credit' => 0,
                        'reference_type' => 'PurchaseOrder',
                        'reference_id' => $purchaseOrder->id,
                        'description' => 'Reverse accounts payable for deleted credit purchase',
                        'date' => now(),
                    ];
                }
            }

            $batch->entries()->createMany($journalEntries);
        });
    }

    public function failed(PurchaseOrderDeleted $event, $exception)
    {
        echo "Failed to reverse journal entries for deleted Purchase Order ID: {$event->purchaseOrder->id}. Error: {$exception->getMessage()}";
    }
}
