<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConversionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('conversions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('sent_from')->nullable()->index(); 
            $table->bigInteger('sent_to')->nullable()->index();
            $table->string('message',191);
            $table->enum('is_read',['read','unread'])->default('unread');
            $table->string('trip_id',30);
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
        Schema::dropIfExists('conversions');
    }
}
