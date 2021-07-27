<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveUserPeopleIdFromBookingInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('booking_infos', function (Blueprint $table) {
            $table->dropForeign(['user_people_id']);
            $table->dropColumn(['user_people_id']);
            $table->bigInteger('user_id')->unsigned()->nullable()->after('booking_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
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
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id']);
            $table->bigInteger('user_people_id')->unsigned()->nullable()->after('booking_id');
            $table->foreign('user_people_id')->references('id')->on('user_peoples')->onDelete('cascade');
        });
    }
}
