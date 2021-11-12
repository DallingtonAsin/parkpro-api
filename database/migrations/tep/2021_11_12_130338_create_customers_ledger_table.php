<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersLedgerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers_ledger', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('type');
            $table->unsignedBigInteger('customer_id');
            $table->text('description');
            $table->double('credit')->default('0');
            $table->double('debt')->default('0');
            $table->double('balance')->generatedAs('credit-debt');
            $table->date('date');
            $table->timestamps();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customers_ledger');
    }
}
