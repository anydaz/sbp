<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnCogsToSalesOrderDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales_order_details', function (Blueprint $table) {
            $table->float('cogs')->nullable();
        });

        $details = App\Models\SalesOrderDetail::get();
        foreach ($details as $detail)
        {
            $product = App\Models\Product::where('id', $detail->product_id)->first();
            $detail->cogs = $product->cogs;
            $detail->save();
        }

        Schema::table('sales_order_details', function (Blueprint $table) {
            $table->float('cogs')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales_order_details', function (Blueprint $table) {
            $table->dropColumn('cogs');
        });
    }
}
