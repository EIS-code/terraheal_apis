<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToStaffTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->date('dob')->after('gender')->nullable()->change();
            $table->enum('login_access',[0, 1])->default(0)->comment('0: Disable, 1: Enable')->after('shop_id');
            $table->enum('status',[0, 1])->default(0)->comment('0: Deactive, 1: Active')->after('login_access');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->string('dob')->nullable()->after('gender')->change();
            $table->dropColumn('login_access');
            $table->dropColumn('status');
        });
    }
}
