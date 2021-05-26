<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTherapistFreeSlotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('therapist_free_slots', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('therapist_id')->unsigned()->nullable();
            $table->foreign('therapist_id')->references('id')->on('therapists')->onDelete('cascade');
            $table->time('startTime');
            $table->time('endTime');
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
        Schema::dropIfExists('therapist_free_slots');
    }
}
