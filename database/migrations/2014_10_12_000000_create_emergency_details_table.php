<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmergencyDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('emergency_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('contact_person',191)->nullable();
            $table->string('image')->nullable();
            $table->string('contact_details')->nullable();
            $table->string('contact_number')->nullable()->index();
            $table->enum('status',['active','inactive'])->default('active');
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
        Schema::dropIfExists('emergency_details');
    }
}
