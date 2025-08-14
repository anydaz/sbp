<?php

namespace App\Listeners;

use App\Events\SalesOrderDeleted;
use App\Models\Account;
use App\Models\JournalBatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleSalesOrderJournalDeletion implements ShouldQueue
{
    public $tries = 3;

    public function handle(SalesOrderDeleted $event)
    {
        $salesOrder = $event->salesOrder;
        $cashAccountId = Account::where('code', '1001')->first()->id; // Cash
        $accountReceivableAccountId = Account::where('code', '1003')->first()->id; // Accounts Receivable
        $salesRevenueAccountId = Account::where('code', '4001')->first()->id;
        $cogsAccountId = Account::where('code', '5001')->first()->id;
        $inventoryAccountId = Account::where('code', '1004')->first()->id;
        
        // Determine payment type based on payment_category_id (1 = Cash, 2 = Credit)
        $paymentType = $salesOrder->payment_category_id == 1 ? 'cash' : 'credit';

        DB::transaction(function () use ($salesOrder, $cashAccountId, $accountReceivableAccountId, $salesRevenueAccountId, $cogsAccountId, $inventoryAccountId, $paymentType) {
            $batch = JournalBatch::create([
                'date' => now(),
                'description' => 'Sale deletion reversal #' . $salesOrder->sales_number,
                'reference_type' => 'SalesOrder',
                'reference_id' => $salesOrder->id,
            ]);

            $batch->entries()->createMany([
                [
                    'account_id' => $paymentType == 'cash' ? $cashAccountId : $accountReceivableAccountId,
                    'debit' => 0,
                    'credit' => $salesOrder->total,
                    'reference_type' => 'SalesOrder',
                    'reference_id' => $salesOrder->id,
                    'description' => $paymentType == 'cash' ? 'Reverse cash received for deleted sale' : 'Reverse accounts receivable for deleted sale',
                    'date' => now(),
                ],
                [
                    'account_id' => $cogsAccountId,
                    'debit' => 0,
                    'credit' => $salesOrder->total_cogs,
                    'reference_type' => 'SalesOrder',
                    'reference_id' => $salesOrder->id,
                    'description' => 'Reverse COGS for deleted sale',
                    'date' => now(),
                ],
                [
                    'account_id' => $salesRevenueAccountId,
                    'debit' => $salesOrder->total,
                    'credit' => 0,
                    'reference_type' => 'SalesOrder',
                    'reference_id' => $salesOrder->id,
                    'description' => 'Reverse revenue for deleted sale',
                    'date' => now(),
                ],
                [
                    'account_id' => $inventoryAccountId,
                    'debit' => $salesOrder->total_cogs,
                    'credit' => 0,
                    'reference_type' => 'SalesOrder',
                    'reference_id' => $salesOrder->id,
                    'description' => 'Reverse inventory reduction for deleted sale',
                    'date' => now(),
                ],
            ]);
        });
    }

    public function failed(SalesOrderDeleted $event, $exception)
    {
        echo "Failed to reverse journal entries for deleted Sales Order ID: {$event->salesOrder->id}. Error: {$exception->getMessage()}";
    }
}
