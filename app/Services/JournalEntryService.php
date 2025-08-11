<?php

namespace App\Services;

use App\Models\Account;
use App\Models\JournalBatch;
use Illuminate\Support\Facades\DB;

class JournalEntryService
{
    public function createDeliveryNoteReversal($deliveryNote, $action)
    {
        $inventoryInTransitAccountId = Account::where('code', '1005')->first()->id; // Inventory in Transit
        $inventoryAccountId = Account::where('code', '1004')->first()->id; // Inventory

        return DB::transaction(function () use ($deliveryNote, $action, $inventoryAccountId, $inventoryInTransitAccountId) {
            $batch = JournalBatch::create([
                'date' => now(),
                'description' => "Delivery Note {$action} reversal #" . $deliveryNote->id,
                'reference_type' => 'DeliveryNote',
                'reference_id' => $deliveryNote->id,
            ]);

            // Reverse the original entries (swap debit and credit)
            $batch->entries()->createMany([
                [
                    'account_id' => $inventoryAccountId,
                    'debit' => 0,
                    'credit' => $deliveryNote->total,
                    'reference_type' => 'DeliveryNote',
                    'reference_id' => $deliveryNote->id,
                    'description' => "Reverse inventory for delivery note {$action}",
                    'date' => now(),
                ],
                [
                    'account_id' => $inventoryInTransitAccountId,
                    'debit' => $deliveryNote->total,
                    'credit' => 0,
                    'reference_type' => 'DeliveryNote',
                    'reference_id' => $deliveryNote->id,
                    'description' => "Reverse inventory in transit for delivery note {$action}",
                    'date' => now(),
                ]
            ]);

            return $batch;
        });
    }
}
