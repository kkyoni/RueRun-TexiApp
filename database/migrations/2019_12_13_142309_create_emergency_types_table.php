<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmergencyTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('emergency_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code',255)->nullable();
            $table->string('type_name',255)->nullable();
            $table->enum('status',['active','inactive'])->default('active')->index();
            $table->string('contact_number',255)->nullable();
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
        Schema::dropIfExists('emergency_types');
    }
}
