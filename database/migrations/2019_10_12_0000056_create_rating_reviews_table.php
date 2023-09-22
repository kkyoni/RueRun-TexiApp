<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRatingReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rating_reviews', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('from_user_id')->index()->nullable();
            $table->foreign('from_user_id')->references('id')->on('users')->onDelete('set null');
            $table->unsignedBigInteger('to_user_id')->index()->nullable();
            $table->foreign('to_user_id')->references('id')->on('users')->onDelete('set null');
            $table->unsignedBigInteger('booking_id')->index()->nullable();
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('set null');
            $table->string('rating',191)->nullable();
            $table->string('comment',191)->nullable();
            $table->enum('is_read_user',['read','unread'])->default('unread');
            $table->enum('is_read_driver',['read','unread'])->default('unread');
            $table->enum('status',['pending', 'approved','rejected'])->default('pending')->index();
            $table->string('behaviors_id',191)->nullable();
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
        Schema::dropIfExists('rating_reviews');
    }
}
