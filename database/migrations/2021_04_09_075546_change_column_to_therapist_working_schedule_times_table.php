<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnToTherapistWorkingScheduleTimesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('therapist_working_schedule_times', function (Blueprint $table) {
            $table->time('start_time')->nullable()->default(null)->change();
            $table->time('end_time')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('therapist_working_schedule_times', function (Blueprint $table) {
            $table->timestamp('start_time')->nullable(false)->change();
            $table->timestamp('end_time')->change();
        });
    }
}
