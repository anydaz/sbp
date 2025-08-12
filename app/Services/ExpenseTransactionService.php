<?php

namespace App\Services;

use App\Models\ExpenseTransaction;
use App\Events\ExpenseTransactionCreated;
use App\Events\ExpenseTransactionUpdated;
use App\Events\ExpenseTransactionDeleted;

class ExpenseTransactionService
{
    public function getAll()
    {
        return ExpenseTransaction::with('account')
            ->orderBy('date', 'desc')
            ->get();
    }

    public function store($data)
    {
        $transaction = ExpenseTransaction::create($data);
        event(new ExpenseTransactionCreated($transaction));
        return $transaction->load('account');
    }

    public function update($id, $data)
    {
        $transaction = ExpenseTransaction::findOrFail($id);
        $transaction->update($data);
        event(new ExpenseTransactionUpdated($transaction));
        return $transaction->load('account');
    }

    public function destroy($id)
    {
        $transaction = ExpenseTransaction::findOrFail($id);
        event(new ExpenseTransactionDeleted($transaction));
        $transaction->delete();
        return $transaction;
    }
}
