<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
          Schema::create('users', function (Blueprint $table) {
                $table->increments('id');
                $table->string('first_name');
                $table->string('last_name');
                $table->string('name');
                $table->string('username')->unique();
                $table->string('gender');
                $table->string('email')->nullable();
                $table->unsignedBigInteger('user_role')->default(1);
                $table->string('tel_no')->unique();
                $table->string('alt_telno')->nullable();
                $table->string('address');
                $table->string('nationalID_no');
                $table->timestamp('email_verified_at')->nullable();
                $table->string('image')->nullable();
                $table->string('password', 255)->default(Hash::make('12345678'));
                $table->integer('loginAttempts')->default(0);
                $table->integer('otpAttempts')->default(0);
                $table->string('OTPcode')->nullable();
                $table->string('isVerified')->default(false);
                $table->boolean('isActive')->default(false);
                $table->string('inactivated_by')->nullable();
                $table->rememberToken()->nullable();
                $table->timestamps();
                $table->foreign('user_role')->references('role_id')->on('roles');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
