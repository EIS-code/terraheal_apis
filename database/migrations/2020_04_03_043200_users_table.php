<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('surname');
            $table->string('dob')->nullable();
            $table->enum('gender', ['m', 'f'])->comment('m: Male, f: Female');
            $table->string('email')->unique();
            $table->string('tel_number_code', 20)->nullable();
            $table->string('tel_number', 50)->nullable();
            $table->string('emergency_tel_number_code', 20)->nullable();
            $table->string('emergency_tel_number', 50)->nullable();
            $table->string('nif')->nullable();
            // $table->string('address')->nullable();
            $table->string('id_passport')->nullable();
            // $table->string('photo');
            $table->string('avatar')->nullable();
            $table->string('avatar_original')->nullable();
            // $table->string('login_by')->nullable();
            $table->string('device_token')->nullable();
            $table->string('device_type')->nullable();
            $table->string('app_version')->nullable();
            $table->string('oauth_uid')->nullable();
            $table->tinyInteger('oauth_provider')->nullable()->comment('1: google, 2: facebook, 3: apple, 4: linkedin');
            $table->string('profile_photo')->nullable();
            $table->string('password')->nullable();
            $table->string('referral_code')->nullable();
            $table->enum('is_deleted', [0, 1])->comment('0: Nope, 1: Yes');
            $table->enum('is_email_verified', ['0', '1'])->default('0')->comment('0: Nope, 1: Yes');
            $table->enum('is_mobile_verified', ['0', '1'])->default('0')->comment('0: Nope, 1: Yes');
            $table->enum('is_document_verified', ['0', '1'])->default('0')->comment('0: Nope, 1: Yes');
            $table->bigInteger('country_id')->unsigned()->nullable();
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
            $table->bigInteger('city_id')->unsigned()->nullable();
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');
            $table->bigInteger('shop_id')->unsigned()->nullable();
            $table->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade');
            $table->timestamps();
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
