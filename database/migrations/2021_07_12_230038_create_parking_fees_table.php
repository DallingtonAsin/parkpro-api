<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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
            $table->unsignedBigInteger('parking_area_id');
            $table->unsignedBigInteger('vehicle_cat_id');
            $table->double('fee_per_hour');
            $table->boolean('is_deleted')->default(false);
            $table->integer('deleted_by')->unsigned()->nullable();
            $table->timestamps();
            $table->foreign('parking_area_id')->references('id')->on('parking_areas')->onDelete('cascade');
            $table->foreign('vehicle_cat_id')->references('id')->on('vehicle_categories')->onDelete('cascade');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('cascade');
      
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
        Schema::dropIfExists('parking_fees');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
