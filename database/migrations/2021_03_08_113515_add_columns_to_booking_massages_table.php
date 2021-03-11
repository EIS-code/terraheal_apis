<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToBookingMassagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('booking_massages', function (Blueprint $table) {
            $table->bigInteger('room_id')->nullable()->unsigned();
            $table->foreign('room_id')->nullable()->references('id')->on('rooms')->onDelete('cascade');
            $table->enum('is_confirm', [0, 1])->default(0)->comment('0: Nope, 1: Yes');
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
            $table->dropForeign('room_id');
            $table->dropColumn('room_id');
        });
    }
}
