<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookingNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('booking_notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('driver_id')->nullable();
            $table->string('booking_id')->nullable();
            $table->string('parcel_id')->nullable();
            $table->string('shuttle_id')->nullable();
            $table->enum('is_send',['0','1'])->defualt('0');
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
        Schema::dropIfExists('booking_notifications');
    }
}
