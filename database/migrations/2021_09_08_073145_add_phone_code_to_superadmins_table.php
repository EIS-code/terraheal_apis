<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPhoneCodeToSuperadminsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('superadmins', function (Blueprint $table) {
            $table->string('tel_number_code', 20)->nullable()->after('tel_number');
            $table->string('emergency_tel_number_code', 20)->nullable()->after('emergency_tel_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('superadmins', function (Blueprint $table) {
            $table->dropColumn('tel_number_code');
            $table->dropColumn('emergency_tel_number_code');
        });
    }
}
