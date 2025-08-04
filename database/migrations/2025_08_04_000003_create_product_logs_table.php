<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductLogsTable extends Migration
{
    public function up()
    {
        Schema::create('product_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('action'); // e.g. 'cogs_calculation', 'quantity_update', etc.
            $table->integer('qty_change')->nullable();
            $table->decimal('price_before', 15, 2)->nullable();
            $table->decimal('price_after', 15, 2)->nullable();
            $table->decimal('cogs_before', 15, 2)->nullable();
            $table->decimal('cogs_after', 15, 2)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_logs');
    }
}
