<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLastEditedToProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // need last edited coz we only need to track the last time a product is edited from master product
        // if the product qty is updated because of transaction like sales / purchaser / return it should not be updated
        Schema::table('products', function (Blueprint $table) {
            $table->timestamp('last_edited')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('last_edited');
        });
    }
}
