<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReceptionistTimeTableTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('receptionist_time_tables', function (Blueprint $table) {
            $table->id();
            $table->date('login_date')->nullable();
            $table->time('login_time')->nullable();
            $table->time('logout_time')->nullable();
            $table->time('break_time')->nullable();
            $table->bigInteger('receptionist_id')->unsigned();
            $table->foreign('receptionist_id')->references('id')->on('receptionists')->onDelete('cascade');
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
        Schema::dropIfExists('receptionist_time_table');
    }
}
