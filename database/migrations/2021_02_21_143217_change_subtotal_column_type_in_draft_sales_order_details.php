<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeSubtotalColumnTypeInDraftSalesOrderDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('draft_sales_order_details', function (Blueprint $table) {
            $table->float('subtotal')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('draft_sales_order_details', function (Blueprint $table) {
            $table->integer('subtotal')->change();
        });
    }
}
