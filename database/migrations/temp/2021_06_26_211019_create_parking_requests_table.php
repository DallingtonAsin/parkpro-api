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
            $table->string('status')->default('PENDING');
            $table->date('request_date');
            $table->date('approval_date')->nullable();
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
        Schema::dropIfExists('parking_requests');
    }
}
