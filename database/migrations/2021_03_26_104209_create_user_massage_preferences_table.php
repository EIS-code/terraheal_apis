<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserMassagePreferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_massage_preferences', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('massage_preference_id')->unsigned();
            $table->foreign('massage_preference_id')->references('id')->on('massage_preferences')->onDelete('cascade');
            $table->bigInteger('mp_option_id')->unsigned()->nullable();
            $table->foreign('mp_option_id')->references('id')->on('massage_preference_options')->onDelete('cascade');
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->text('answer')->nullable();
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
        Schema::dropIfExists('user_massage_preferences');
    }
}
