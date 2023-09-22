<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParcelImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parcel_images', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('parcel_id')->index()->nullable();
            $table->foreign('parcel_id')->references('id')->on('parcel_details')->onDelete('set null');
            $table->string('image_name')->nullable();
            $table->string('image')->nullable();
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
        Schema::dropIfExists('parcel_images');
    }
}
