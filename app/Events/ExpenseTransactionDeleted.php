<?php

namespace App\Events;

use App\Models\ExpenseTransaction;
use Illuminate\Foundation\Events\Dispatchable;

class ExpenseTransactionDeleted
{
    use Dispatchable;

    public $transaction;

    public function __construct(ExpenseTransaction $transaction)
    {
        $this->transaction = $transaction;
    }
}
