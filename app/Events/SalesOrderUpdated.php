<?php

namespace App\Events;

use App\Models\SalesOrder;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SalesOrderUpdated
{
    use Dispatchable, SerializesModels;

    public $salesOrder;
    public $originalSalesOrder;

    public function __construct(SalesOrder $salesOrder, SalesOrder $originalSalesOrder)
    {
        $this->salesOrder = $salesOrder;
        $this->originalSalesOrder = $originalSalesOrder;
    }
}
