<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLinerideUserBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lineride_user_bookings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('user_id')->nullable();
            $table->string('driver_id')->nullable();
            $table->string('shuttle_driver_id')->nullable();
            $table->string('pick_up_location')->nullable();
            $table->string('drop_location')->nullable();
            $table->string('start_latitude')->nullable();
            $table->string('start_longitude')->nullable();
            $table->string('drop_latitude')->nullable();
            $table->string('drop_longitude')->nullable();
            $table->string('start_time')->nullable();
            $table->string('end_time')->nullable();
            $table->string('vehicle_id')->nullable();
            $table->string('ride_setting_id')->nullable();
            $table->string('total_distance')->nullable();
            $table->string('admin_commision')->nullable();
            $table->enum('trip_status',['pending','on_going','driver_arrived','completed','rejected','accepted','departed'])->defualt('pending');
            $table->enum('booking_type',['immediate','schedule'])->defualt('immediate')->index();
            $table->string('extra_notes')->nullable();
            $table->string('promo_id')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('otp')->nullable();
            $table->string('total_amount')->nullable();
            $table->enum('payment_type',['card','wallet','cash'])->nullable();
            $table->string('booking_date')->nullable();
            $table->string('booking_start_time')->nullable();
            $table->string('booking_end_time')->nullable();
            $table->string('tip_amount')->nullable();
            $table->string('airport_charge')->nullable();
            $table->string('toll_amount')->nullable();
            $table->enum('taxi_hailing',['sharing','no_sharing'])->default('no_sharing');
            $table->string('total_luggage')->nullable();
            $table->string('seat_available')->nullable();
            $table->string('seat_booked')->nullable();
            $table->string('radius')->nullable();
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
        Schema::dropIfExists('lineride_user_bookings');
    }
}
