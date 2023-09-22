<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDriverVehicleDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('driver_vehicle_documents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('driver_id')->index()->nullable();
            $table->foreign('driver_id')->references('id')->on('users')->onDelete('set null');
            $table->unsignedBigInteger('vehicle_id')->index()->nullable();
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('set null');
            $table->string('document_name',200)->nullable();
            $table->string('document_doc',200)->nullable();
            $table->enum('doc_type',['registration','insurance','odometer'])->nullable();
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
        Schema::dropIfExists('driver_vehicle_documents');
    }
}
