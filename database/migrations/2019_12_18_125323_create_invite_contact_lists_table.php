<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInviteContactListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invite_contact_lists', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('user_id')->nullable()->index();
            $table->string('email')->nullable()->index();
            $table->string('contact_number')->nullable()->index();
            $table->longtext('description')->nullable();
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
        Schema::dropIfExists('invite_contact_lists');
    }
}
