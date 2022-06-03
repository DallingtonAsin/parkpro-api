<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('country_iso_code')->default('UG');
            $table->string('country_code')->default('+256');
            $table->string('phone_number')->unique();
            $table->string('email')->nullable();
            $table->double('account_balance')->default('0');
            $table->string('image')->nullable();
            $table->string('otp')->nullable();
            $table->string('unique_device_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('current_version')->nullable();
            $table->boolean('profile_status')->default(0);
            $table->boolean('is_active')->default(false);
            $table->boolean('is_blocked')->default(false);
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken()->nullable();
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
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists('customers');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
