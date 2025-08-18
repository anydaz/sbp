<?php

namespace App\Listeners;

use App\Events\PurchaseOrderDeleted;
use App\Services\JournalService;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandlePurchaseOrderJournalDeletion implements ShouldQueue
{
    public $tries = 3;

    protected $journalService;

    public function __construct(JournalService $journalService)
    {
        $this->journalService = $journalService;
    }

    public function handle(PurchaseOrderDeleted $event)
    {
        $purchaseOrder = $event->purchaseOrder;

        $reversalBatch = $this->journalService->reverseJournalEntries(
            'PurchaseOrder',
            $purchaseOrder->id,
            'Purchase deletion reversal #' . $purchaseOrder->purchase_number
        );
    }

    public function failed(PurchaseOrderDeleted $event, $exception)
    {
        echo "Failed to reverse journal entries for deleted Purchase Order ID: {$event->purchaseOrder->id}. Error: {$exception->getMessage()}";
    }
}
