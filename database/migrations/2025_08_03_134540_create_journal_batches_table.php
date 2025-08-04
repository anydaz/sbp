<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJournalBatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('journal_batches', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('description')->nullable();
            $table->string('reference_type'); // e.g., 'Sale', 'Purchase'
            $table->integer('reference_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('journal_batches');
    }
}
