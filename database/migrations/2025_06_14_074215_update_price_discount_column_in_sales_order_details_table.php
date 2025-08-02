<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePriceDiscountColumnInSalesOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales_order_details', function (Blueprint $table) {
            $table->decimal('price', 15, 2)->change();
            $table->decimal('item_discount', 15, 2)->change();
            $table->decimal('subtotal', 15, 2)->change();
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
            $table->integer('price')->change();
            $table->integer('item_discount')->change();
            $table->integer('subtotal')->change();
        });
    }
}
