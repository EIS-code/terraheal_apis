<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsWorkingToReceptionistTimeTablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('receptionist_time_tables', function (Blueprint $table) {
            $table->enum('is_working',[0,1])->comment('0: No, 1: Yes')->default(0)->after('receptionist_id');
            $table->string('absent_reason')->nullable()->after('is_working');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('receptionist_time_tables', function (Blueprint $table) {
            $table->dropColumn('is_working');
            $table->dropColumn('absent_reason');
        });
    }
}
