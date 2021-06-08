<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToTherapistWorkingSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('therapist_working_schedules', function (Blueprint $table) {
            $table->bigInteger('shift_id')->unsigned()->after('therapist_id');
            $table->foreign('shift_id')->references('id')->on('shop_shifts')->onDelete('cascade');
            $table->enum('is_working', ['0', '1'])->default('0')->comment("0: No, 1: Yes")->after('shift_id');
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
            $table->dropForeign(['shift_id']);
            $table->dropColumn(['shift_id']);
            $table->dropColumn('is_working');
        });
    }
}
