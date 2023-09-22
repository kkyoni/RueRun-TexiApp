<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->unsignedBigInteger('driver_id')->index()->nullable();
            $table->foreign('driver_id')->references('id')->on('users')->onDelete('set null');
            $table->string('pick_up_location',191)->nullable();
            $table->string('drop_location',191)->nullable();
            $table->string('start_time',191)->nullable();
            $table->string('end_time',191)->nullable();
            $table->string('ride_setting_id',191)->nullable();
            $table->string('hold_time',191)->nullable();
            $table->string('vehicle_id',191)->nullable();
            $table->integer('base_fare')->nullable();
            $table->string('total_km',191)->nullable();
            $table->string('admin_commision',191)->nullable();
            $table->string('transaction_id',191)->nullable();
            $table->enum('trip_status',['pending','on_going','driver_arrived','completed','cancelled','accepted','departed'])->defualt('pending');
            $table->enum('booking_type',['immediate','schedule','out_of_town'])->nullable();
            $table->string('extra_notes',191)->nullable();
            $table->unsignedBigInteger('promo_id')->index()->nullable();
            $table->foreign('promo_id')->references('id')->on('promocodes')->onDelete('set null');
            $table->string('latitude',191)->nullable();
            $table->string('longitude',191)->nullable();
            $table->string('otp',191)->nullable();
            $table->string('total_amount',191)->nullable();
            $table->enum('payment_type',['card','wallet','cash'])->nullable();
            $table->string('start_latitude',191)->nullable();
            $table->string('start_longitude',191)->nullable();
            $table->string('drop_latitude',191)->nullable();
            $table->string('drop_longitude',191)->nullable();
            $table->timestamp('start_date')->nullable();
            $table->string('booking_date',191)->nullable();
            $table->string('booking_start_time',191)->nullable();
            $table->string('booking_end_time',191)->nullable();
            $table->string('hold_time_amount',191)->nullable();
            $table->string('tip_amount',191)->nullable();
            $table->string('airport_charge',191)->nullable();
            $table->string('toll_amount',191)->nullable();
            $table->enum('taxi_hailing',['sharing','no_sharing','vip','seat_selection'])->default('no_sharing');
            $table->string('service_id',191)->nullable();
            $table->string('total_luggage',191)->nullable();
            $table->string('seats',191)->nullable();
            $table->string('card_id',191)->nullable();
            $table->enum('payment_status',['pending','completed'])->defualt('pending');
            $table->enum('admin_comm_status',['pending', 'cancelled', 'transferred'])->default('pending');
            $table->enum('tip_amount_status',['pending', 'cancelled', 'transferred'])->default('pending');
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
        Schema::dropIfExists('bookings');
    }
}
