<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveColumnsFromTherapistWorkingScheduleBreaksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('therapist_working_schedule_breaks', function (Blueprint $table) {
            $table->dropForeign(['schedule_time_id']);
            $table->dropColumn(['schedule_time_id']);
            $table->dropColumn('break_reason');
            $table->dropColumn('break_for');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('therapist_working_schedule_breaks', function (Blueprint $table) {
            $table->bigInteger('schedule_time_id')->unsigned();
            $table->foreign('schedule_time_id')->references('id')->on('therapist_working_schedule_times')->onDelete('cascade');
            $table->enum('break_for', ['0', '1', '2'])->default('0')->comment('0: Other reason, 1: For lunch, 2: For dinner');
            $table->text('break_reason')->nullable();
        });
    }
}
