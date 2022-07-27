<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameTranreferenceInMobileMoneyTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mobile_money_transactions', function (Blueprint $table) {
            $table->renameColumn('tranReference','transReference');
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
            $table->renameColumn('tranReference','transReference');
        });
    }
}
