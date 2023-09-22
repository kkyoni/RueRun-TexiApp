<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDriverDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('driver_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('driver_id')->index()->nullable();
            $table->foreign('driver_id')->references('id')->on('users')->onDelete('set null');
            $table->string('vehicle_model')->nullable();
            $table->string('vehicle_plate')->nullable();
            $table->string('color')->nullable();
            $table->string('vehicle_image')->nullable();
            $table->string('year')->nullable();
            $table->string('mileage')->nullable();
            $table->string('ride_type')->nullable();
            $table->enum('type',['driver','user'])->defualt('driver');
            $table->string('seat')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('driver_details');
    }
}
