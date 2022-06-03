<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMobileMoneyTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mobile_money_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customerId');
            $table->string('type');
            $table->string('phoneNumber');
            $table->double('amount');
            $table->string('orderId');
            $table->string('tranReference');
            $table->boolean('smsSent')->default(false);
            $table->string('status');
            $table->integer('failureCount')->default(0);
            $table->string('errorMessage')->nullable();
            $table->date('errorDate')->nullable();
            $table->datetime("date");
            $table->string("userIpAddress", 20);
            $table->string("serverIpAddress", 20);
            $table->timestamps();
            $table->foreign('customerId')->references('id')->on('customers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists('mobile_money_transactions');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
