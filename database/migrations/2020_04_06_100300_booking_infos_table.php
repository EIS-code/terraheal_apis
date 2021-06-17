<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BookingInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('booking_infos', function (Blueprint $table) {
            $table->id();
            $table->string('location');
            $table->date('massage_date')->nullable();
            $table->dateTime('massage_time')->nullable();
            $table->enum('is_cancelled', [0, 1])->default(0)->comment('0: Nope, 1: Yes');
            $table->string('cancelled_reason')->nullable();
            $table->enum('imc_type', [1, 2])->comment('1: ASAP, 2: Scheduled');
            // $table->string('massage_timing', 50);
            // $table->float('massage_pricing');
            /*$table->float('price');
            $table->float('cost');
            $table->float('origional_price');
            $table->float('origional_cost');*/
            $table->enum('is_done', [0, 1])->default(0)->comment('0: Nope, 1: Yes');
            // $table->decimal('exchange_rate', 8, 4);
            $table->bigInteger('booking_currency_id')->unsigned();
            $table->foreign('booking_currency_id')->references('id')->on('currencies')->onDelete('cascade');
            $table->bigInteger('shop_currency_id')->unsigned();
            $table->foreign('shop_currency_id')->references('id')->on('currencies')->onDelete('cascade');            
            $table->bigInteger('therapist_id')->unsigned();
            $table->foreign('therapist_id')->references('id')->on('therapists')->onDelete('cascade');
            $table->bigInteger('booking_id')->unsigned();
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
            $table->bigInteger('room_id')->nullable()->unsigned();
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('cascade');
            $table->bigInteger('user_people_id')->nullable()->unsigned();
            $table->foreign('user_people_id')->references('id')->on('user_peoples')->onDelete('cascade');
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
        Schema::dropIfExists('booking_infos');
    }
}
