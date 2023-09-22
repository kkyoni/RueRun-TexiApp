<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('sent_from_user')->nullable()->index();
            $table->bigInteger('sent_to_user')->nullable()->index();
            $table->string('title',255)->nullable();
            $table->bigInteger('booking_id')->nullable()->index();
            $table->string('parcel_id')->nullable()->index();
            $table->longtext('description')->nullable();
            $table->enum('is_read',['unread','read'])->default('unread')->index();
            $table->enum('admin_flag',['0','1'])->default('0')->index();
            $table->enum('notification_for',['pending','accepted','cancel','completed'])->nullable();
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
        Schema::dropIfExists('user_notifications');
    }
}
