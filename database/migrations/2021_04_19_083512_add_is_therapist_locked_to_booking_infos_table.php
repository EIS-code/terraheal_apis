<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsTherapistLockedToBookingInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('booking_infos', function (Blueprint $table) {
            $table->enum('is_therapist_locked',[0,1])->default(0)->after('user_people_id')->comment('0:No, 1:Yes');
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
            $table->dropColumn('is_therapist_locked');
        });
    }
}
