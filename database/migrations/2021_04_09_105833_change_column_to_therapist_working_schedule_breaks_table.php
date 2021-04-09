<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnToTherapistWorkingScheduleBreaksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('therapist_working_schedule_breaks', function (Blueprint $table) {
            $table->time('from')->nullable()->default(null)->change();
            $table->time('to')->change();
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
            $table->timestamp('from')->nullable(false)->change();
            $table->timestamp('to')->change();
        });
    }
}
