<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateParkingAreasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parking_areas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('client_id');
            $table->string('name');
            $table->string('phone_number', 15);
            $table->string('address');
            $table->text('description')->nullable();
            $table->time('opens_at');
            $table->time('closes_at');
            $table->double('latitude');
            $table->double('longitude');
            $table->double('rating')->default('0');
            $table->integer('total_space');
            $table->integer('current_free_space')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->integer('deleted_by')->unsigned()->nullable();
            $table->timestamps();
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
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
        Schema::dropIfExists('parking_areas');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
