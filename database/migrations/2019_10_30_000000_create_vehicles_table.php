<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVehiclesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable();
            $table->string('vehicle_image')->nullable();
            $table->integer('base_fare')->nullable();
            $table->double('price_per_km',10,2)->nullable();
            $table->double('extra_cost_dropdown',10,2)->nullable();
            $table->enum('extra_cost_include',['yes','no'])->nullable();
            $table->unsignedBigInteger('vehicle_type_id')->nullable();
            $table->unsignedBigInteger('vehicle_model_id')->nullable();
            $table->enum('status',['active','inactive'])->default('active');
            $table->string('cancellation_time_in_minutes')->nullable();
            $table->string('cancellation_charge_in_per')->nullable();
            $table->string('total_seat')->nullable();
            $table->string('price_per_seat')->nullable();
            $table->string('wheel_type')->nullable();
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
        Schema::dropIfExists('vehicles');
    }
}
