<?php

namespace App\Listeners;

use App\Events\SalesOrderCreated;
use App\Models\Account;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\DB;
use App\Models\JournalBatch;

use Illuminate\Contracts\Queue\ShouldQueue;

class CreateJournalEntry implements ShouldQueue
{
    public $tries = 3;
    public function handle(SalesOrderCreated $event)
    {
        $salesOrder = $event->salesOrder;
        $cashAccountId = Account::where('code', '1001')->first()->id; // Assuming '1001' is the cash account code
        $salesRevenueAccountId = Account::where('code', '4001')->first()->id; // Assuming '4001' is the sales revenue account code

        DB::transaction(function () use ($salesOrder, $cashAccountId, $salesRevenueAccountId) {
            $batch = JournalBatch::create([
                'date' => now(),
                'description' => 'Sale transaction #' . $salesOrder->sales_number,
                'reference_type' => 'Sale',
                'reference_id' => $salesOrder->id,
            ]);

            $batch->entries()->createMany([
                [
                    'account_id' => $cashAccountId,
                    'debit' => $salesOrder->total,
                    'credit' => 0,
                    'reference_type' => 'Sale',
                    'reference_id' => $salesOrder->id,
                    'description' => 'Cash received for sale',
                    'date' => now(),
                ],
                [
                    'account_id' => $salesRevenueAccountId,
                    'debit' => 0,
                    'credit' => $salesOrder->total,
                    'reference_type' => 'Sale',
                    'reference_id' => $salesOrder->id,
                    'description' => 'Revenue from sale',
                    'date' => now(),
                ]
            ]);
        });
    }

    public function failed(SalesOrderCreated $event, $exception)
    {
        // Handle the failure, e.g., log the error or notify the admin
        echo "Failed to create journal entry for Sales Order ID: {$event->salesOrder->id}. Error: {$exception->getMessage()}";
    }
}
