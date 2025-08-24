<?php

namespace App\Events;

use App\Models\PurchasePayment;
use Illuminate\Foundation\Events\Dispatchable;

class PurchasePaymentCreated
{
    use Dispatchable;

    public $payment;

    public function __construct(PurchasePayment $payment)
    {
        $this->payment = $payment;
    }
}
