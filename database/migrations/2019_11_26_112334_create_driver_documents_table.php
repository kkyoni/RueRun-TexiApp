<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDriverDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('driver_documents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('driver_id')->index()->nullable();
            $table->foreign('driver_id')->references('id')->on('users')->onDelete('set null');
            $table->string('doc_image',200)->nullable();
            $table->string('doc_name',200)->nullable();
            $table->unsignedBigInteger('vehicle_id')->index()->nullable();
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('set null');
            $table->enum('doc_type',['licence','w9','driving_report'])->nullable();
            $table->string('ssn_no',200)->nullable();
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
        Schema::dropIfExists('driver_documents');
    }
}
