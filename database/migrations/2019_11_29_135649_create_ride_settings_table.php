<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRideSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ride_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code')->nullable()->index();
            $table->string('name')->nullable()->index();
            $table->enum('status',['active','inactive'])->default('active');
            $table->longtext('city_list')->nullable();
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
        Schema::dropIfExists('ride_settings');
    }
}
