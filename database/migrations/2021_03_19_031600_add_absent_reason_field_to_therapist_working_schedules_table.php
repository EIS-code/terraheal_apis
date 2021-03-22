<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAbsentReasonFieldToTherapistWorkingSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('therapist_working_schedules', function (Blueprint $table) {
            $table->string('absent_reason')->nullable()->after('is_absent');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('therapist_working_schedules', function (Blueprint $table) {
            $table->dropColumn('absent_reason');
        });
    }
}
