<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountBalancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_balances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->date('balance_date');
            $table->decimal('debit_balance', 15, 2)->default(0);
            $table->decimal('credit_balance', 15, 2)->default(0);
            $table->decimal('net_balance', 15, 2)->default(0);
            $table->string('period_type')->default('monthly'); // monthly, yearly, daily
            $table->string('period_identifier'); // 2025-01, 2025, 2025-01-15
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->index(['account_id', 'balance_date']);
            $table->index(['balance_date', 'period_type']);
            $table->unique(['account_id', 'period_identifier', 'period_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('account_balances');
    }
}
