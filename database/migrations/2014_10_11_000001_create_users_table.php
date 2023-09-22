<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('uuid')->unique()->nullable()->index();
            $table->enum('sign_up_as',['web','other','fb','google','app'])->nullable();
            $table->enum('user_type',['superadmin','user','driver','company','sub_admin'])->nullable();
            $table->enum('driver_signup_as',['individual','company'])->default('individual')->index();
            $table->enum('login_status',['online','offline'])->nullable();
            $table->string('social_id',30)->nullable();
            $table->string('social_media',191)->nullable();
            $table->string('ref_id')->nullable();
            $table->string('device_token')->nullable();
            $table->enum('device_type',['ios','android'])->nullable();
            $table->bigInteger('company_id')->nullable()->index();
            $table->string('first_name',30)->nullable();
            $table->string('last_name',30)->nullable();
            $table->string('company_name',30)->nullable();
            $table->string('email')->nullable()->index();
            $table->string('avatar')->default('default.png');
            $table->string('password')->nullable();
            $table->string('country_code',255)->nullable();
            $table->string('contact_number')->nullable()->index();
            $table->enum('gender',['male','female'])->nullable();
            $table->string('address')->nullable();
            $table->string('add_latitude')->nullable();
            $table->string('add_longitude')->nullable();
            $table->string('country')->nullable();
            $table->string('state_id')->nullable();
            $table->string('city_id')->nullable();
            $table->string('latitude',255)->nullable();
            $table->string('longitude',255)->nullable();
            $table->string('link_expire')->nullable();
            $table->string('driver_company_id')->nullable();
            $table->enum('status',['active','inactive'])->default('active');
            $table->enum('doc_status',['reject','pending','approved'])->default('pending');
            $table->string('link_code')->nullable();
            $table->enum('availability_status',['on','off'])->default('on');
            $table->string('vehicle_id')->nullable();
            $table->enum('driver_doc',['0','1'])->default('0');
            $table->enum('car_doc',['0','1'])->default('0');
            $table->enum('vehicle_doc_status',['reject','pending','approved'])->default('pending')->index();
            $table->rememberToken();
            $table->text('reason_for_inactive')->nullable();
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
            Schema::dropIfExists('users');
        }
    }
