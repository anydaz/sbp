<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddColumnEfficacyCodeToProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('efficacy_code')->nullable();
        });

        // Use MySQL-compatible UPDATE JOIN syntax
        DB::statement('UPDATE products JOIN products p2 ON products.id = p2.id SET products.efficacy_code = p2.barcode');


        Schema::table('products', function (Blueprint $table) {
            $table->unique('efficacy_code');
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
            $table->dropColumn('efficacy_code');
        });
    }
}
