<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToManagerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('manager', function (Blueprint $table) {
            $table->enum('gender', ['m', 'f'])->nullable()->after('email')->comment('m: Male, f: Female');
            $table->string('dob')->nullable()->after('gender');
            $table->string('tel_number', 50)->nullable()->after('dob')->unique();
            $table->string('emergency_tel_number', 50)->nullable()->after('tel_number');
            $table->string('nif')->nullable()->after('emergency_tel_number');
            $table->string('id_passport')->nullable()->after('nif');
            $table->enum('is_email_verified', ['0', '1'])->default('0')->comment('0: Nope, 1: Yes')->after('remember_token');
            $table->enum('is_mobile_verified', ['0', '1'])->default('0')->comment('0: Nope, 1: Yes')->after('is_email_verified');
            $table->text('news')->nullable()->after('is_mobile_verified');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('manager', function (Blueprint $table) {
            $table->dropColumn('gender');
            $table->dropColumn('dob');
            $table->dropColumn('tel_number');
            $table->dropColumn('emergency_tel_number');
            $table->dropColumn('nif');
            $table->dropColumn('id_passport');
            $table->dropColumn('is_email_verified');
            $table->dropColumn('is_mobile_verified');
            $table->dropColumn('news');
        });
    }
}
