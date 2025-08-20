<?php

namespace App\Listeners;

use App\Events\SalesOrderUpdated;
use App\Models\Account;
use App\Services\JournalService;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleSalesOrderJournalUpdate implements ShouldQueue
{
    public $tries = 3;

    protected $journalService;

    public function __construct(JournalService $journalService)
    {
        $this->journalService = $journalService;
    }

    public function handle(SalesOrderUpdated $event)
    {
        $salesOrder = $event->salesOrder;
        $originalSalesOrder = $event->originalSalesOrder;

        $cashAccountId = Account::where('code', '1001')->first()->id; // Cash
        $accountReceivableAccountId = Account::where('code', '1003')->first()->id; // Accounts Receivable
        $salesRevenueAccountId = Account::where('code', '4001')->first()->id; // Sales Revenue
        $cogsAccountId = Account::where('code', '5001')->first()->id; // COGS
        $inventoryAccountId = Account::where('code', '1004')->first()->id; // Inventory

        // Determine payment type for new sales order
        $newPaymentType = $salesOrder->payment_category_id == 1 ? 'cash' : 'credit';

        DB::transaction(function () use ($salesOrder, $cashAccountId, $accountReceivableAccountId, $salesRevenueAccountId, $cogsAccountId, $inventoryAccountId, $newPaymentType) {
            // Reverse the latest journal batch for this sales order
            $this->journalService->reverseJournalEntries(
                'SalesOrder',
                $salesOrder->id,
                'Sale reversal for update #' . $salesOrder->sales_number
            );

            // Create new entries based on the updated sales order
            $journalEntries = [
                [
                    'account_id' => $newPaymentType == 'cash' ? $cashAccountId : $accountReceivableAccountId,
                    'debit' => $salesOrder->total,
                    'credit' => 0,
                    'reference_type' => 'SalesOrder',
                    'reference_id' => $salesOrder->id,
                    'description' => $newPaymentType == 'cash' ? 'Updated cash received for sale' : 'Updated accounts receivable for sale',
                    'date' => now(),
                ],
                [
                    'account_id' => $cogsAccountId,
                    'debit' => $salesOrder->total_cogs,
                    'credit' => 0,
                    'reference_type' => 'SalesOrder',
                    'reference_id' => $salesOrder->id,
                    'description' => 'Updated COGS for sale',
                    'date' => now(),
                ],
                [
                    'account_id' => $salesRevenueAccountId,
                    'debit' => 0,
                    'credit' => $salesOrder->total,
                    'reference_type' => 'SalesOrder',
                    'reference_id' => $salesOrder->id,
                    'description' => 'Updated revenue from sale',
                    'date' => now(),
                ],
                [
                    'account_id' => $inventoryAccountId,
                    'debit' => 0,
                    'credit' => $salesOrder->total_cogs,
                    'reference_type' => 'SalesOrder',
                    'reference_id' => $salesOrder->id,
                    'description' => 'Updated inventory reduction for sale',
                    'date' => now(),
                ]
            ];

            // Create the new journal batch with entries
            $this->journalService->createJournalBatch([
                'date' => now(),
                'description' => 'Updated sale transaction #' . $salesOrder->sales_number,
                'reference_type' => 'SalesOrder',
                'reference_id' => $salesOrder->id,
            ], $journalEntries);
        });
    }

    public function failed(SalesOrderUpdated $event, $exception)
    {
        echo "Failed to update journal entries for Sales Order ID: {$event->salesOrder->id}. Error: {$exception->getMessage()}";
    }
}
