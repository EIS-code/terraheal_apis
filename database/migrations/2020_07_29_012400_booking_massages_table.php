<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BookingMassagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('booking_massages', function (Blueprint $table) {
            $table->id();
            $table->float('price');
            $table->float('cost');
            $table->float('origional_price');
            $table->float('origional_cost');
            $table->decimal('exchange_rate', 8, 4);
            $table->string('notes_of_injuries')->nullable();
            $table->bigInteger('booking_info_id')->unsigned();
            $table->foreign('booking_info_id')->references('id')->on('booking_infos')->onDelete('cascade');
            $table->bigInteger('pressure_preference')->nullable()->unsigned();
            $table->foreign('pressure_preference')->references('id')->on('massage_preference_options')->onDelete('cascade');
            $table->bigInteger('gender_preference')->unsigned();
            $table->foreign('gender_preference')->references('id')->on('massage_preference_options')->onDelete('cascade');
            $table->bigInteger('focus_area_preference')->unsigned();
            $table->foreign('focus_area_preference')->references('id')->on('massage_preference_options')->onDelete('cascade');
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
        Schema::dropIfExists('booking_massages');
    }
}
