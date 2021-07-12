<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveColumnsFromBookingInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('booking_infos', function (Blueprint $table) {
            $table->dropColumn('massage_date');
            $table->dropColumn('massage_time');
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
            $table->date('massage_date')->nullable();
            $table->dateTime('massage_time')->nullable();
        });
    }
}
