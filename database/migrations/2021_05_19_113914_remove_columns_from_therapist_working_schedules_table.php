<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveColumnsFromTherapistWorkingSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('therapist_working_schedules', function (Blueprint $table) {
            $table->dropColumn('is_working');
            $table->dropColumn('is_absent');
            $table->dropColumn('absent_reason');
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
            $table->enum('is_working', ['0', '1'])->default('0')->comment("0: Nope, 1: Yes");
            $table->enum('is_absent', ['0', '1'])->nullable()->comment("0: Nope, 1: Yes");
            $table->string('absent_reason')->nullable()->after('is_absent');
        });
    }
}
