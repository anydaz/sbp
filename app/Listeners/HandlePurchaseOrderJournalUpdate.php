<?php

namespace App\Listeners;

use App\Events\PurchaseOrderUpdated;
use App\Models\Account;
use App\Models\JournalBatch;
use App\Services\JournalService;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandlePurchaseOrderJournalUpdate implements ShouldQueue
{
    public $tries = 3;

    protected $journalService;

    public function __construct(JournalService $journalService)
    {
        $this->journalService = $journalService;
    }

    public function handle(PurchaseOrderUpdated $event)
    {
        $purchaseOrder = $event->purchaseOrder;
        $originalPurchaseOrder = $event->originalPurchaseOrder;
        $cashAccountId = Account::where('code', '1001')->first()->id; // Cash
        $accountPayableAccountId = Account::where('code', '2001')->first()->id; // Accounts Payable
        $inventoryInTransitAccountId = Account::where('code', '1005')->first()->id; // Inventory in Transit

        // Determine payment type for new purchase order
        $newPaymentType = $purchaseOrder->payment_category_id == 1 ? 'cash' : 'credit';

        DB::transaction(function () use ($purchaseOrder, $cashAccountId, $accountPayableAccountId, $inventoryInTransitAccountId, $newPaymentType) {
            // Reverse all existing journal entries for this purchase order
            $this->journalService->reverseJournalEntries(
                'PurchaseOrder',
                $purchaseOrder->id,
                'Purchase reversal for update #' . $purchaseOrder->purchase_number
            );

            // Create new entries based on the updated purchase order
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

            // Create the new journal batch with entries
            $this->journalService->createJournalBatch([
                'date' => $purchaseOrder->date,
                'description' => 'Updated purchase transaction #' . $purchaseOrder->purchase_number,
                'reference_type' => 'PurchaseOrder',
                'reference_id' => $purchaseOrder->id,
            ], $journalEntries);
        });
    }

    public function failed(PurchaseOrderUpdated $event, $exception)
    {
        echo "Failed to update journal entries for Purchase Order ID: {$event->purchaseOrder->id}. Error: {$exception->getMessage()}";
    }
}
