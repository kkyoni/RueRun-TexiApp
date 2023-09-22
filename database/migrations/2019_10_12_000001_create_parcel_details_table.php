<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParcelDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parcel_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->unsignedBigInteger('driver_id')->index()->nullable();
            $table->foreign('driver_id')->references('id')->on('users')->onDelete('set null');
            $table->string('otp',191)->nullable();
            $table->string('recepient_name',191)->nullable();
            $table->string('contact_number',191)->nullable();
            $table->longtext('description')->nullable();
            $table->string('ride_setting_id')->index()->nullable();
            $table->string('pick_up_location',191)->nullable();
            $table->string('drop_location',191)->nullable();
            $table->string('start_latitude',191)->nullable();
            $table->string('start_longitude',191)->nullable();
            $table->string('drop_latitude',191)->nullable();
            $table->string('drop_longitude',191)->nullable();
            $table->string('start_time',191)->nullable();
            $table->string('end_time',191)->nullable();
            $table->string('package_type')->nullable();
            $table->string('parcel_length',191)->nullable();
            $table->string('parcel_deep',191)->nullable();
            $table->string('parcel_height',191)->nullable();
            $table->string('parcel_weight',191)->nullable();
            $table->string('hold_time',191)->nullable();
            $table->integer('base_fare')->nullable();
            $table->string('total_distance',191)->nullable();
            $table->string('admin_commision',191)->nullable();
            $table->enum('parcel_status',['pending','on_going','driver_arrived','completed','cancelled','accepted'])->defualt('pending');
            $table->string('extra_notes',191)->nullable();
            $table->string('promo_id',191)->nullable();
            $table->string('total_amount',191)->nullable();
            $table->enum('payment_type',['card','wallet','cash'])->nullable();
            $table->string('booking_date',191)->nullable();
            $table->string('booking_start_time',191)->nullable();
            $table->string('booking_end_time',191)->nullable();
            $table->string('hold_time_amount',191)->nullable();
            $table->string('tip_amount',191)->nullable();
            $table->string('airport_charge',191)->nullable();
            $table->string('toll_amount',191)->nullable();
            $table->enum('booking_type',['immediate','schedule'])->nullable();
            $table->string('transaction_id',191)->nullable();
            $table->string('card_id',191)->nullable();
            $table->enum('payment_status',['pending','completed'])->defualt('pending');
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
        Schema::dropIfExists('parcel_details');
    }
}
