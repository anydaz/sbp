<?php

namespace App\Listeners;

use App\Events\SalesOrderUpdated;
use App\Models\Account;
use App\Models\JournalBatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleSalesOrderJournalUpdate implements ShouldQueue
{
    public $tries = 3;

    public function handle(SalesOrderUpdated $event)
    {
        $salesOrder = $event->salesOrder;
        $originalSalesOrder = $event->originalSalesOrder;

        $cashAccountId = Account::where('code', '1001')->first()->id; // Cash
        $accountReceivableAccountId = Account::where('code', '1003')->first()->id; // Accounts Receivable
        $salesRevenueAccountId = Account::where('code', '4001')->first()->id;
        $cogsAccountId = Account::where('code', '5001')->first()->id;
        $inventoryAccountId = Account::where('code', '1004')->first()->id;
        
        // Determine payment types for both original and new sales orders
        $originalPaymentType = $originalSalesOrder->payment_category_id == 1 ? 'cash' : 'credit';
        $newPaymentType = $salesOrder->payment_category_id == 1 ? 'cash' : 'credit';

        DB::transaction(function () use ($salesOrder, $originalSalesOrder, $cashAccountId, $accountReceivableAccountId, $salesRevenueAccountId, $cogsAccountId, $inventoryAccountId, $originalPaymentType, $newPaymentType) {
            // First create reversal entries for the original amounts
            $reversalBatch = JournalBatch::create([
                'date' => now(),
                'description' => 'Sale reversal for update #' . $salesOrder->sales_number,
                'reference_type' => 'SalesOrder',
                'reference_id' => $salesOrder->id,
            ]);

            $reversalBatch->entries()->createMany([
                [
                    'account_id' => $originalPaymentType == 'cash' ? $cashAccountId : $accountReceivableAccountId,
                    'debit' => 0,
                    'credit' => $originalSalesOrder->total,
                    'reference_type' => 'SalesOrder',
                    'reference_id' => $salesOrder->id,
                    'description' => $originalPaymentType == 'cash' ? 'Reverse cash received for updated sale' : 'Reverse accounts receivable for updated sale',
                    'date' => now(),
                ],
                [
                    'account_id' => $cogsAccountId,
                    'debit' => 0,
                    'credit' => $originalSalesOrder->total_cogs,
                    'reference_type' => 'SalesOrder',
                    'reference_id' => $salesOrder->id,
                    'description' => 'Reverse COGS for updated sale',
                    'date' => now(),
                ],
                [
                    'account_id' => $salesRevenueAccountId,
                    'debit' => $originalSalesOrder->total,
                    'credit' => 0,
                    'reference_type' => 'SalesOrder',
                    'reference_id' => $salesOrder->id,
                    'description' => 'Reverse revenue for updated sale',
                    'date' => now(),
                ],
                [
                    'account_id' => $inventoryAccountId,
                    'debit' => $originalSalesOrder->total_cogs,
                    'credit' => 0,
                    'reference_type' => 'SalesOrder',
                    'reference_id' => $salesOrder->id,
                    'description' => 'Reverse inventory reduction for updated sale',
                    'date' => now(),
                ],
            ]);

            // Then create new entries for the updated amounts
            $newBatch = JournalBatch::create([
                'date' => now(),
                'description' => 'Updated sale transaction #' . $salesOrder->sales_number,
                'reference_type' => 'SalesOrder',
                'reference_id' => $salesOrder->id,
            ]);

            $newBatch->entries()->createMany([
                [
                    'account_id' => $newPaymentType == 'cash' ? $cashAccountId : $accountReceivableAccountId,
                    'debit' => $salesOrder->total,
                    'credit' => 0,
                    'reference_type' => 'SalesOrder',
                    'reference_id' => $salesOrder->id,
                    'description' => $newPaymentType == 'cash' ? 'Cash received for updated sale' : 'Accounts receivable for updated sale',
                    'date' => now(),
                ],
                [
                    'account_id' => $cogsAccountId,
                    'debit' => $salesOrder->total_cogs,
                    'credit' => 0,
                    'reference_type' => 'SalesOrder',
                    'reference_id' => $salesOrder->id,
                    'description' => 'COGS for updated sale',
                    'date' => now(),
                ],
                [
                    'account_id' => $salesRevenueAccountId,
                    'debit' => 0,
                    'credit' => $salesOrder->total,
                    'reference_type' => 'SalesOrder',
                    'reference_id' => $salesOrder->id,
                    'description' => 'Revenue from updated sale',
                    'date' => now(),
                ],
                [
                    'account_id' => $inventoryAccountId,
                    'debit' => 0,
                    'credit' => $salesOrder->total_cogs,
                    'reference_type' => 'SalesOrder',
                    'reference_id' => $salesOrder->id,
                    'description' => 'Inventory reduction for updated sale',
                    'date' => now(),
                ],
            ]);
        });
    }

    public function failed(SalesOrderUpdated $event, $exception)
    {
        echo "Failed to update journal entries for Sales Order ID: {$event->salesOrder->id}. Error: {$exception->getMessage()}";
    }
}
