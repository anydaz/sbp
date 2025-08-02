<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveUnusedColumnOnProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            // Remove unused columns
            $table->dropColumn(['alias', 'barcode', 'efficiency_code', 'code', 'min', 'plus']);
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
            // Re-add the columns if needed
            $table->string('alias')->nullable();
            $table->string('barcode')->nullable();
            $table->string('efficiency_code')->nullable();
            $table->string('code')->nullable();
            $table->integer('min')->nullable();
            $table->integer('plus')->nullable();
        });
    }
}
