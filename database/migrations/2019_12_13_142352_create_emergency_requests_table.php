<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmergencyRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('emergency_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type_id')->nullable();
            $table->string('driver_id')->nullable();
            $table->string('user_id')->nullable();
            $table->string('booking_id')->nullable();
            $table->longtext('extra_notes')->nullable();
            $table->enum('status',['pending','on_going','resolved'])->default('pending')->index();
            $table->enum('view_status',['0','1'])->default('0')->index();
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
        Schema::dropIfExists('emergency_requests');
    }
}
