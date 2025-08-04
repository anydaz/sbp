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
        $cogsAccountId = Account::where('code', '5001')->first()->id; // Assuming '5001' is the COGS account code
        $inventoryAccountId = Account::where('code', '1004')->first()->id; // Assuming '1004' is the inventory account code

        DB::transaction(function () use ($salesOrder, $cashAccountId, $salesRevenueAccountId, $cogsAccountId, $inventoryAccountId) {
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
                    'account_id' => $cogsAccountId,
                    'debit' => $salesOrder->total_cogs,
                    'credit' => 0,
                    'reference_type' => 'Sale',
                    'reference_id' => $salesOrder->id,
                    'description' => 'COGS for sale',
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
                ],
                [
                    'account_id' => $inventoryAccountId,
                    'debit' => 0,
                    'credit' => $salesOrder->total_cogs,
                    'reference_type' => 'Sale',
                    'reference_id' => $salesOrder->id,
                    'description' => 'Inventory reduction for sale',
                    'date' => now(),
                ],
            ]);
        });
    }

    public function failed(SalesOrderCreated $event, $exception)
    {
        // Handle the failure, e.g., log the error or notify the admin
        echo "Failed to create journal entry for Sales Order ID: {$event->salesOrder->id}. Error: {$exception->getMessage()}";
    }
}
