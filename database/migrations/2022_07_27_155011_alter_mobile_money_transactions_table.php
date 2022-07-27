<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterMobileMoneyTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mobile_money_transactions', function (Blueprint $table) {

            $table->string('requestId')->after("orderId")->nullable();
            $table->string('transactionId')->after("tranReference")->nullable();

            $table->string('currency')->after("transactionId")->nullable();
            $table->double('chargedAmount')->after("currency")->nullable();
            $table->double('appFee')->after("chargedAmount")->nullable();
            $table->double('merchantFee')->after("appFee")->nullable();

            $table->string('processorResponse')->after("merchantFee")->nullable();
            $table->string('authModel')->after("processorResponse")->nullable();
            $table->string('narration')->after("authModel")->nullable();

            $table->string('paymentType')->after("narration")->nullable();
            $table->string('paymentDate')->after("paymentType")->nullable();
            $table->string('accountId')->after("paymentDate")->nullable();
            $table->double('amountSettled')->after("accountId")->nullable();
            $table->string('transIpAddress')->after("amountSettled")->nullable();

            $table->string('transCustomerId')->after("transIpAddress")->nullable();
            $table->string('transCustomerName')->after("transCustomerId")->nullable();
            $table->string('transCustomerPhoneNumber')->after("transCustomerName")->nullable();
            $table->string('transCustomerEmail')->after("transCustomerPhoneNumber")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mobile_money_transactions', function (Blueprint $table) {
            $table->dropColumn('requestId');
            $table->dropColumn('transactionId');

            $table->dropColumn('currency');
            $table->dropColumn('chargedAmount');
            $table->dropColumn('appFee');
            $table->dropColumn('merchantFee');

            $table->dropColumn('processorResponse');
            $table->dropColumn('authModel');
            $table->dropColumn('narration');

            $table->dropColumn('paymentType');
            $table->dropColumn('paymentDate');
            $table->dropColumn('accountId');
            $table->dropColumn('amountSettled');
            $table->dropColumn('transIpAddress');

            $table->dropColumn('transCustomerId');
            $table->dropColumn('transCustomerName');
            $table->dropColumn('transCustomerPhoneNumber');
            $table->dropColumn('transCustomerEmail');
        });
    }
}
