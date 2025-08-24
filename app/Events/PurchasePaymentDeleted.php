<?php

namespace App\Events;

use App\Models\PurchasePayment;
use Illuminate\Foundation\Events\Dispatchable;

class PurchasePaymentDeleted
{
    use Dispatchable;

    public $payment;

    public function __construct(PurchasePayment $payment)
    {
        $this->payment = $payment;
    }
}
