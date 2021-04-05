<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pack_services', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('pack_id')->unsigned();
            $table->foreign('pack_id')->references('id')->on('packs')->onDelete('cascade');
            $table->bigInteger('massage_id')->unsigned()->nullable();
            $table->foreign('massage_id')->references('id')->on('massages')->onDelete('cascade');
            $table->bigInteger('massage_timing_id')->unsigned()->nullable();
            $table->foreign('massage_timing_id')->references('id')->on('massage_timings')->onDelete('cascade');
            $table->bigInteger('therapy_id')->unsigned()->nullable();
            $table->foreign('therapy_id')->references('id')->on('therapies')->onDelete('cascade');
            $table->bigInteger('therapy_timing_id')->unsigned()->nullable();
            $table->foreign('therapy_timing_id')->references('id')->on('therapies_timings')->onDelete('cascade');
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
        Schema::dropIfExists('pack_services');
    }
}
