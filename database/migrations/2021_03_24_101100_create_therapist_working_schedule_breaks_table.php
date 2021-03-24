<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTherapistWorkingScheduleBreaksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('therapist_working_schedule_breaks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamp('from');
            $table->timestamp('to')->nullable();
            $table->enum('break_for', ['0', '1', '2'])->default('0')->comment('0: Other reason, 1: For lunch, 2: For dinner');
            $table->text('break_reason')->nullable();
            $table->bigInteger('schedule_id')->unsigned();
            $table->foreign('schedule_id')->references('id')->on('therapist_working_schedules')->onDelete('cascade');
            $table->bigInteger('schedule_time_id')->unsigned();
            $table->foreign('schedule_time_id')->references('id')->on('therapist_working_schedule_times')->onDelete('cascade');
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
        Schema::drop('therapist_working_schedule_breaks');
    }
}
