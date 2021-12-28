<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveTherapistIdFromBookingInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('booking_infos', function (Blueprint $table) {
            $table->dropForeign(['therapist_id']);
            $table->dropColumn(['therapist_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('booking_infos', function (Blueprint $table) {
            $table->bigInteger('therapist_id')->unsigned()->nullable();
            $table->foreign('therapist_id')->references('id')->on('therapists')->onDelete('cascade');
        });
    }
}
