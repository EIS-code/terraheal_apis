<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToSuperadminsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('superadmins', function (Blueprint $table) {
            $table->enum('gender', ['m', 'f'])->nullable()->comment('m: Male, f: Female')->after('email');
            $table->string('dob')->nullable()->after('gender');
            $table->string('tel_number', 50)->nullable()->after('dob')->unique();
            $table->string('emergency_tel_number', 50)->nullable()->after('tel_number');
            $table->string('nif')->nullable()->after('emergency_tel_number');
            $table->string('id_passport')->nullable()->after('nif');
            $table->bigInteger('country_id')->unsigned()->nullable()->after('id_passport');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
            $table->bigInteger('city_id')->unsigned()->nullable()->after('country_id');
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');
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
            $table->dropColumn('gender');
            $table->dropColumn('dob');
            $table->dropColumn('tel_number');
            $table->dropColumn('emergency_tel_number');
            $table->dropColumn('nif');
            $table->dropColumn('id_passport');
            $table->dropColumn('country_id');
            $table->dropColumn('city_id');
        });
    }
}
