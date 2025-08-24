<?php

namespace App\Listeners;

use App\Events\PurchasePaymentCreated;
use App\Events\PurchasePaymentUpdated;
use App\Events\PurchasePaymentDeleted;
use App\Services\JournalService;
use App\Models\Account;

class PurchasePaymentJournalListener
{
    protected $journalService;

    public function __construct(JournalService $journalService)
    {
        $this->journalService = $journalService;
    }

    public function handleCreated(PurchasePaymentCreated $event)
    {
        $payment = $event->payment;

        // Get accounts
        $accountsPayableId = Account::where('code', '2001')->first()->id; // Accounts Payable
        $cashAccountId = Account::where('code', '1001')->first()->id; // Cash account

        // Prepare journal entries for purchase payment creation
        // Debit Accounts Payable (reduces liability)
        // Credit Cash (reduces asset)
        $journalEntries = [
            [
                'date' => $payment->payment_date,
                'account_id' => $accountsPayableId,  // Accounts Payable
                'debit' => $payment->amount,
                'credit' => 0,
                'reference_type' => 'PurchasePayment',
                'reference_id' => $payment->id,
                'description' => 'Purchase Payment for PO #' . $payment->purchase_order->po_number,
            ],
            [
                'date' => $payment->payment_date,
                'account_id' => $cashAccountId,  // Cash account
                'debit' => 0,
                'credit' => $payment->amount,
                'reference_type' => 'PurchasePayment',
                'reference_id' => $payment->id,
                'description' => 'Purchase Payment for PO #' . $payment->purchase_order->po_number,
            ]
        ];

        // Create the journal batch with entries using the service
        $this->journalService->createJournalBatch([
            'date' => $payment->payment_date,
            'description' => 'Purchase Payment for PO #' . $payment->purchase_order->po_number,
            'reference_type' => 'PurchasePayment',
            'reference_id' => $payment->id,
        ], $journalEntries);
    }

    public function handleUpdated(PurchasePaymentUpdated $event)
    {
        $payment = $event->payment;

        // Reverse the latest journal batch for this purchase payment
        $this->journalService->reverseJournalEntries(
            'PurchasePayment',
            $payment->id,
            'Purchase Payment update reversal for PO #' . $payment->purchase_order->po_number
        );

        // Create new entries based on the updated purchase payment
        $this->handleCreated(new PurchasePaymentCreated($payment));
    }

    public function handleDeleted(PurchasePaymentDeleted $event)
    {
        $payment = $event->payment;

        // Reverse the latest journal batch for this purchase payment
        $this->journalService->reverseJournalEntries(
            'PurchasePayment',
            $payment->id,
            'Purchase Payment deletion reversal for PO #' . $payment->purchase_order->po_number
        );
    }

    public function subscribe($events)
    {
        $events->listen(
            PurchasePaymentCreated::class,
            [PurchasePaymentJournalListener::class, 'handleCreated']
        );

        $events->listen(
            PurchasePaymentUpdated::class,
            [PurchasePaymentJournalListener::class, 'handleUpdated']
        );

        $events->listen(
            PurchasePaymentDeleted::class,
            [PurchasePaymentJournalListener::class, 'handleDeleted']
        );
    }
}
