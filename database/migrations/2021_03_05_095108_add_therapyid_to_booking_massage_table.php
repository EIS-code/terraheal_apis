<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTherapyidToBookingMassageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('booking_massages', function (Blueprint $table) {
            $table->bigInteger('therapy_id')->nullable()->unsigned();
            $table->foreign('therapy_id')->nullable()->references('id')->on('therapies')->onDelete('cascade');

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
            $table->dropForeign('therapy_id');
            $table->dropColumn('therapy_id');
        });
    }
}
