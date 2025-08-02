<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FillPurchaseNumberColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Get all purchase orders and update them individually for better compatibility
        $purchaseOrders = \DB::table('purchase_orders')->whereNull('purchase_number')->get();

        foreach ($purchaseOrders as $purchaseOrder) {
            \DB::table('purchase_orders')
                ->where('id', $purchaseOrder->id)
                ->update([
                    'purchase_number' => "PO/**/120725/" . $purchaseOrder->id
                ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Reset purchase_number to null for all records
        \DB::table('purchase_orders')->update([
            'purchase_number' => null
        ]);
    }
}
