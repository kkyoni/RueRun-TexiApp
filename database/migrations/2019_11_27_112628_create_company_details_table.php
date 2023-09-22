<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index()->nullable();
            $table->foreign('company_id')->references('id')->on('users')->onDelete('set null');
            $table->string('recipient_name',191)->nullable();
            $table->string('job_title',191)->nullable();
            $table->string('company_size',191)->nullable();
            $table->string('website',191)->nullable();
            $table->enum('type',['user','driver'])->default('driver')->index();
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
        Schema::dropIfExists('company_details');
    }
}
