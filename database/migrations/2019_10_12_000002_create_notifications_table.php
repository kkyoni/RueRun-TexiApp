<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->unsignedBigInteger('driver_id')->index()->nullable();
            $table->foreign('driver_id')->references('id')->on('users')->onDelete('set null');
            $table->unsignedBigInteger('booking_id')->index()->nullable();
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('set null');
            $table->string('title',255)->nullable();
            $table->longtext('description')->nullable();
            $table->enum('is_read_user',['read','unread'])->nullable();
            $table->enum('is_read_driver',['read','unread'])->nullable();
            $table->string('booking_date',255)->nullable();
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
        Schema::dropIfExists('notifications');
    }
}
