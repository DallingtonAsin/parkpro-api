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
            $table->id();
            $table->string('ticket_no')->nullable();
            $table->string('telephone_no', 15);
            $table->string('vehicle_number');
            $table->unsignedBigInteger('vehicle_type_id');
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('parking_area_id');
            $table->time('start_time');
            $table->time('end_time');
            $table->double('parking_hours');
            $table->double('amount');
            $table->string('status')->default('PENDING');
            $table->timestamp('request_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('approval_date')->nullable();
            $table->timestamp('reject_date')->nullable();
            $table->foreign('client_id')->references('id')->on('clients');
            $table->foreign('vehicle_type_id')->references('id')->on('vehicle_categories');
            $table->foreign('parking_area_id')->references('id')->on('parking_areas');
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
