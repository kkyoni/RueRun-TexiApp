<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParcelPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parcel_packages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('parcelbooking_id')->nullable();
            $table->string('parcel_length')->nullable();
            $table->string('parcel_deep')->nullable();
            $table->string('parcel_height')->nullable();
            $table->string('parcel_weight')->nullable();
            $table->string('total_amount')->nullable();
            $table->string('package_type')->nullable();
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
        Schema::dropIfExists('parcel_packages');
    }
}
