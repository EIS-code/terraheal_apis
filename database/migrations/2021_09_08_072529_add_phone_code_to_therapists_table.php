<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPhoneCodeToTherapistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('therapists', function (Blueprint $table) {
            $table->string('mobile_number_code', 20)->nullable()->after('mobile_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('therapists', function (Blueprint $table) {
            $table->dropColumn('mobile_number_code');
        });
    }
}
