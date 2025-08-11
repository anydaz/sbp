<?php

namespace App\Events;

use App\Models\PurchaseOrder;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PurchaseOrderUpdated
{
    use Dispatchable, SerializesModels;

    public $purchaseOrder;
    public $originalPurchaseOrder;

    public function __construct(PurchaseOrder $purchaseOrder, PurchaseOrder $originalPurchaseOrder)
    {
        $this->purchaseOrder = $purchaseOrder;
        $this->originalPurchaseOrder = $originalPurchaseOrder;
    }
}
