<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParkingFeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parking_fees', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('client_id')->default(1);
            $table->unsignedBigInteger('parking_area_id')->default(1);
            $table->unsignedBigInteger('vehicle_cat_id')->default(1);
            $table->double('fee');
            $table->timestamps();
            $table->foreign('client_id')->references('id')->on('clients');
            $table->foreign('parking_area_id')->references('id')->on('parking_areas')->onDelete('cascade');
            $table->foreign('vehicle_cat_id')->references('id')->on('vehicle_categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('parking_fees');
    }
}
