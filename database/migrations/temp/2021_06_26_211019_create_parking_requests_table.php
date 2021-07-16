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
            $table->string('username', 255);
            $table->string('telephone', 15);
            $table->string('car_number');
            $table->double('parking_hours');
            $table->double('amount');
            $table->string('status')->default('PENDING');
            $table->timestamp('request_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('approval_date')->nullable();
            $table->timestamp('reject_date')->nullable();
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
