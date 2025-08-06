<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Doctrine\DBAL\Types\FloatType;
use Doctrine\DBAL\Types\Type;


class ChangeShippingCostPerItemToDouble extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Type::hasType('double')) {
            Type::addType('double', FloatType::class);
        }

        Schema::table('purchase_orders', function (Blueprint $table) {
            // update existing shipping_cost_per_item to high precision
            // to avoid rounding issues in calculations
            $table->double('shipping_cost_per_item', 15, 8)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // update back to decimal if needed
            $table->decimal('shipping_cost_per_item', 15, 2)->default(0)->change();
        });
    }
}
