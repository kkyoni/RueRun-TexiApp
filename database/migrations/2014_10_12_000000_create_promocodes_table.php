<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePromocodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promocodes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('promo_code',191)->nullable()->index();
            $table->integer('amount')->nullable()->index();
            $table->string('start_date')->nullable()->index();
            $table->string('end_date')->nullable()->index();
            $table->string('description')->nullable();
            $table->enum('status',['active','inactive'])->default('active')->index();
            $table->string('promo_for_users')->nullable();
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
        Schema::dropIfExists('promocodes');
    }
}
