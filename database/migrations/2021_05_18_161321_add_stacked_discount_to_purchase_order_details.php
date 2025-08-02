<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStackedDiscountToPurchaseOrderDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_order_details', function (Blueprint $table) {
            $table->float('discount_percentage1')->nullable();;
            $table->float('discount_percentage2')->nullable();;
            $table->float('discount_percentage3')->nullable();;
        });

        DB::insert('Update purchase_order_details detail SET discount_percentage1 = (detail.item_discount / detail.price * 100)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_order_details', function (Blueprint $table) {
            $table->dropColumn('discount_percentage1')->nullable();;
            $table->dropColumn('discount_percentage2')->nullable();;
            $table->dropColumn('discount_percentage3')->nullable();;
        });
    }
}
