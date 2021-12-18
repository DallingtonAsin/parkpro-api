<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParkingRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parking_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('order_no');
            $table->unsignedBigInteger('parking_area_id');
            $table->string('telephone_no', 15);
            $table->string('vehicle_details');
            $table->unsignedBigInteger('vehicle_cat_id');
            $table->time('start_time');
            $table->time('end_time');
            $table->double('parking_hours');
            $table->double('amount');
            $table->string('status')->default('PENDING');
            $table->timestamp('request_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('approval_date')->nullable();
            $table->timestamp('reject_date')->nullable();
            $table->timestamps();
        });

        Schema::table('parking_requests', function (Blueprint $table) {
            $table->foreign('vehicle_cat_id')->references('id')->on('vehicle_categories')->onDelete('cascade');
            $table->foreign('parking_area_id')->references('id')->on('parking_areas')->onDelete('cascade');
        });



    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('parking_requests');
    }
}
