<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('booking_id')->index()->nullable();
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('set null');
            $table->string('parcel_id')->nullable();
            $table->integer('amount')->nullable();
            $table->unsignedBigInteger('user_id')->index()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->unsignedBigInteger('promo_id')->index()->nullable();
            $table->foreign('promo_id')->references('id')->on('promocodes')->onDelete('set null');
            $table->string('transaction_company',191)->nullable();
            $table->string('transaction_id')->nullable();
            $table->enum('status',['pending','complete','cancelled','reject'])->default('pending')->index();
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
        Schema::dropIfExists('transaction_details');
    }
}
