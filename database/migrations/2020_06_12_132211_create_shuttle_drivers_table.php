<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShuttleDriversTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shuttle_drivers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('shuttle_id')->nullable();
            $table->string('driver_id')->nullable();
            $table->string('user_id')->nullable();
            $table->string('driver_amount')->nullable();
            $table->enum('status',['confirm','rejected','accepted','pending'])->defualt('pending');
            $table->enum('user_confirm',['confirm','rejected','pending'])->defualt('pending');
            $table->string('request_type')->default('shuttle')->nullable();
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
        Schema::dropIfExists('shuttle_drivers');
    }
}
