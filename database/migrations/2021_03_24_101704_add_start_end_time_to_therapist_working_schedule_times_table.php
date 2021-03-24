<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStartEndTimeToTherapistWorkingScheduleTimesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('therapist_working_schedule_times', function (Blueprint $table) {
            DB::statement('ALTER TABLE `therapist_working_schedule_times` CHANGE COLUMN `time` `start_time` TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() AFTER `id`;');
            DB::statement('ALTER TABLE `therapist_working_schedule_times` ADD UNIQUE INDEX `unique_schedule_id` (`schedule_id`);');
            $table->timestamp('end_time')->nullable()->after('start_time');
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
            DB::statement('ALTER TABLE `therapist_working_schedule_times` MODIFY `start_time` `time` TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() AFTER `id`;');
            DB::statement('ALTER TABLE `therapist_working_schedule_times` DROP INDEX `unique_schedule_id`;');
            $table->dropColumn('end_time');
        });
    }
}
