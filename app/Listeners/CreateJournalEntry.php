<?php

namespace App\Listeners;

use App\Events\SalesOrderCreated;
use App\Models\Account;
use App\Services\JournalService;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreateJournalEntry implements ShouldQueue
{
    public $tries = 3;

    protected $journalService;

    public function __construct(JournalService $journalService)
    {
        $this->journalService = $journalService;
    }

    public function handle(SalesOrderCreated $event)
    {
        $salesOrder = $event->salesOrder;

        $cashAccountId = Account::where('code', '1001')->first()->id; // Cash
        $accountReceivableAccountId = Account::where('code', '1003')->first()->id; // Accounts Receivable
        $salesRevenueAccountId = Account::where('code', '4001')->first()->id; // Sales Revenue
        $cogsAccountId = Account::where('code', '5001')->first()->id; // COGS
        $inventoryAccountId = Account::where('code', '1004')->first()->id; // Inventory
        $type = $salesOrder->payment_category_id == 1 ? 'cash' : 'credit';

        // Prepare journal entries for sales order creation
        $journalEntries = [
            [
                'account_id' => $type == 'cash' ? $cashAccountId : $accountReceivableAccountId,
                'debit' => $salesOrder->total,
                'credit' => 0,
                'reference_type' => 'SalesOrder',
                'reference_id' => $salesOrder->id,
                'description' => $type == 'cash' ? 'Cash received for sale' : 'Accounts receivable for sale',
                'date' => now(),
            ],
            [
                'account_id' => $cogsAccountId,
                'debit' => $salesOrder->total_cogs,
                'credit' => 0,
                'reference_type' => 'SalesOrder',
                'reference_id' => $salesOrder->id,
                'description' => 'COGS for sale',
                'date' => now(),
            ],
            [
                'account_id' => $salesRevenueAccountId,
                'debit' => 0,
                'credit' => $salesOrder->total,
                'reference_type' => 'SalesOrder',
                'reference_id' => $salesOrder->id,
                'description' => 'Revenue from sale',
                'date' => now(),
            ],
            [
                'account_id' => $inventoryAccountId,
                'debit' => 0,
                'credit' => $salesOrder->total_cogs,
                'reference_type' => 'SalesOrder',
                'reference_id' => $salesOrder->id,
                'description' => 'Inventory reduction for sale',
                'date' => now(),
            ]
        ];

        // Create the journal batch with entries using the service
        $this->journalService->createJournalBatch([
            'date' => now(),
            'description' => 'Sale transaction #' . $salesOrder->sales_number,
            'reference_type' => 'SalesOrder',
            'reference_id' => $salesOrder->id,
        ], $journalEntries);
    }

    public function failed(SalesOrderCreated $event, $exception)
    {
        // Handle the failure, e.g., log the error or notify the admin
        echo "Failed to create journal entry for Sales Order ID: {$event->salesOrder->id}. Error: {$exception->getMessage()}";
    }
}
