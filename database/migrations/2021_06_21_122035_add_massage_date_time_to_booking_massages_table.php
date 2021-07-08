<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMassageDateTimeToBookingMassagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('booking_massages', function (Blueprint $table) {
            $table->timestamp('massage_date_time')->after('id')->nullable();
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
            $table->dropColumn('massage_date_time');
        });
    }
}
