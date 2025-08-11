<?php

namespace App\Events;

use App\Models\SalesOrder;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SalesOrderDeleted
{
    use Dispatchable, SerializesModels;

    public $salesOrder;

    public function __construct(SalesOrder $salesOrder)
    {
        $this->salesOrder = $salesOrder;
    }
}
