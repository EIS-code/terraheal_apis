<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCancelTypeToBookingInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('booking_infos', function (Blueprint $table) {
            $table->enum('cancel_type',[0,1,2,3,4])->nullable()->after('is_cancelled')
                    ->comment('0: No show, 1: No availability, 2: Double booking 3: Wrong booking, 4: Other reason');
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
            $table->dropColumn('cancel_type');
        });
    }
}
