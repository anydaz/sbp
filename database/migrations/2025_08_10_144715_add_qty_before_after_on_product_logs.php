<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQtyBeforeAfterOnProductLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_logs', function (Blueprint $table) {
            $table->integer('qty_before')->default(0);
            $table->integer('qty_after')->default(0);
            $table->dropColumn('qty_change');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_logs', function (Blueprint $table) {
            $table->dropColumn('qty_before');
            $table->dropColumn('qty_after');
            $table->integer('qty_change')->default(0);
        });
    }
}
