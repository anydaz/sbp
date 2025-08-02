<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDraftSalesOrderIdColumnToDraftSalesOrderDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('draft_sales_order_details', function (Blueprint $table) {
            $table->string('draft_sales_order_id');
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
            $table->dropColumn('draft_sales_order_id');
        });
    }
}
