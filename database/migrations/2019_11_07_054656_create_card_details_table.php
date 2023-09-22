<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCardDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('card_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->string('card_type',191)->nullable()->index();
            $table->string('card_number',191)->nullable()->index();
            $table->string('card_holder_name',191)->nullable();
            $table->string('card_expiry_month',191)->nullable();
            $table->string('card_expiry_year',191)->nullable();
            $table->string('billing_address',191)->nullable();
            $table->string('bank_name',191)->nullable();
            $table->string('card_name',191)->nullable();
            $table->string('cvv',191)->nullable();
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
        Schema::dropIfExists('card_details');
    }
}
