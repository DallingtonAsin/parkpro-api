<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_details', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->string('abbreviation')->nullable();
                $table->string('mobile_no')->unique();
                $table->string('email')->unique();
                $table->string('address');
                $table->string('motto')->nullable();
                $table->string('logo')->nullable();
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
        Schema::dropIfExists('company_details');
    }
}
