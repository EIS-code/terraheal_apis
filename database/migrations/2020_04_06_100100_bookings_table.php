<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->enum('booking_type', [1, 2])->comment('1: In Massage Center, 2: Home / Hotel Visit.');
            $table->string('special_notes')->nullable();
            // $table->integer('total_persons')->nullable();
            $table->enum('bring_table_futon', [0, 1, 2])->default(0)->comment('0: None, 1: Table, 2: Tatami/Futon');
            $table->integer('table_futon_quantity')->default(0);
            $table->bigInteger('session_id')->unsigned();
            $table->foreign('session_id')->references('id')->on('session_types')->onDelete('cascade');
            $table->string('copy_with_id')->nullable();
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('shop_id')->unsigned();
            $table->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade');
            // $table->rememberToken();
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
        Schema::dropIfExists('bookings');
    }
}
