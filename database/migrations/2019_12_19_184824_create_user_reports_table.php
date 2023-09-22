<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_reports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('from_user_id')->nullable()->index();
            $table->string('to_user_id')->nullable()->index();
            $table->string('booking_id')->nullable()->index();
            $table->string('behaviors_id')->nullable()->index();
            $table->string('title')->nullable()->index();
            $table->longtext('description')->nullable();
            $table->string('date')->nullable()->index();
            $table->string('time')->nullable()->index();
            $table->longText('admin_comments')->nullable();
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
        Schema::dropIfExists('user_reports');
    }
}
