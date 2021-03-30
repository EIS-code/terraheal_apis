<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColumnsToBookingMassagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('booking_massages', function (Blueprint $table) {
            $table->dropForeign(['therapy_id']);
            $table->dropColumn(['therapy_id']);
            $table->bigInteger('massage_timing_id')->unsigned()->nullable()->change();
            $table->bigInteger('massage_prices_id')->unsigned()->nullable()->change();
            $table->bigInteger('therapy_timing_id')->unsigned()->nullable();
            $table->foreign('therapy_timing_id')->references('id')->on('therapies_timings')->onDelete('cascade');
            $table->bigInteger('therapy_prices_id')->unsigned()->nullable();
            $table->foreign('therapy_prices_id')->references('id')->on('therapies_prices')->onDelete('cascade');
	
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('booking_massages', function (Blueprint $table) {
            $table->bigInteger('therapy_id')->nullable()->unsigned();
            $table->foreign('therapy_id')->nullable()->references('id')->on('therapies')->onDelete('cascade');
            $table->dropForeign(['therapy_timing_id']);
            $table->dropColumn(['therapy_timing_id']);
            $table->dropForeign(['therapy_prices_id']);
            $table->dropColumn(['therapy_prices_id']);
            $table->bigInteger('massage_timing_id')->unsigned()->change();
            $table->bigInteger('massage_prices_id ')->unsigned()->change();
        });
    }
}
