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
                $table->unsignedBigInteger('role')->default(1);
                $table->string('mobile_no')->unique();
                $table->string('address');
                $table->string('national_id_no')->nullable();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('photo_path')->nullable();
                $table->string('photo_name')->nullable();
                $table->string('password', 255)->default(Hash::make('12345678'));
                $table->string('otp_code')->nullable();
                $table->integer('login_attempts')->default(0);
                $table->integer('otp_attempts')->default(0);
                $table->string('isVerified')->default(false);
                $table->boolean('is_active')->default(false);
                $table->string('changed_by')->nullable();
                $table->rememberToken()->nullable();
                $table->timestamps();
                $table->foreign('role')->references('id')->on('roles');
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
