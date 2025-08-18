<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReversalColumnsToJournalBatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('journal_batches', function (Blueprint $table) {
            $table->boolean('is_reversal_entries')->default(false)->after('reference_id');
            $table->integer('reversal_reference_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('journal_batches', function (Blueprint $table) {
            $table->dropColumn(['is_reversal_entries', 'reversal_reference_id']);
        });
    }
}
