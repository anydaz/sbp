<?php

namespace App\Listeners;

use App\Events\SalesOrderDeleted;
use App\Services\JournalService;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleSalesOrderJournalDeletion implements ShouldQueue
{
    public $tries = 3;

    protected $journalService;

    public function __construct(JournalService $journalService)
    {
        $this->journalService = $journalService;
    }

    public function handle(SalesOrderDeleted $event)
    {
        $salesOrder = $event->salesOrder;

        // Reverse the latest journal batch for this sales order
        $this->journalService->reverseJournalEntries(
            'SalesOrder',
            $salesOrder->id,
            'Sale deletion reversal #' . $salesOrder->sales_number
        );
    }

    public function failed(SalesOrderDeleted $event, $exception)
    {
        echo "Failed to reverse journal entries for deleted Sales Order ID: {$event->salesOrder->id}. Error: {$exception->getMessage()}";
    }
}
