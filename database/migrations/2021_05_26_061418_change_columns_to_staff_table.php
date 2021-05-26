<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnsToStaffTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->dropColumn('tel_number');
            $table->dropColumn('whatsapp_number');
            $table->dropColumn('photo');
            $table->dropColumn('upload_id');
            $table->dropColumn('insurance');
            $table->string('full_name')->after('id');
            $table->string('password')->after('full_name');
            $table->enum('gender', ['m','f'])->comment('m: Male, f: Female')->nullable()->after('password');
            $table->string('dob')->nullable()->after('gender');
            $table->string('mobile_number', 50)->nullable()->after('dob');
            $table->string('emergency_number', 50)->nullable()->after('mobile_number');
            $table->string('nif', 50)->nullable()->after('emergency_number');
            $table->enum('role', [0, 1])->comment('0: Receptionist, 1: Cleaning lady')->nullable()->after('nif');
            $table->text('address')->nullable()->after('role');
            $table->bigInteger('country_id')->unsigned()->after('role')->nullable();
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
            $table->bigInteger('city_id')->unsigned()->after('country_id')->nullable();
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');
            $table->string('security_number', 50)->nullable()->after('city_id');
            $table->string('bank_name')->nullable()->after('security_number');
            $table->string('account_number', 50)->nullable()->after('bank_name');
            $table->string('language_spoken')->nullable()->after('account_number');
            $table->string('health_condition')->nullable()->after('language_spoken');
            $table->enum('pay_scale', [0, 1])->comment('0: Fixed monthly, 1: Fixed hourly')->nullable()->after('health_condition');
            $table->float('amount')->nullable()->after('pay_scale');
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
            $table->string('name');
            $table->string('tel_number', 50)->nullable();
            $table->string('whatsapp_number', 50)->nullable();
            $table->string('photo')->nullable();
            $table->string('upload_id')->nullable();
            $table->string('insurance')->nullable();
            $table->dropColumn('full_name');
            $table->dropColumn('password');
            $table->dropColumn('gender');
            $table->dropColumn('dob');
            $table->dropColumn('mobile_number');
            $table->dropColumn('emergency_number');
            $table->dropColumn('nif');
            $table->dropColumn('role');
            $table->dropColumn('address');
            $table->dropColumn('security_number');
            $table->dropColumn('bank_name');
            $table->dropColumn('account_number');
            $table->dropColumn('language_spoken');
            $table->dropColumn('health_condition');
            $table->dropColumn('pay_scale');
            $table->dropColumn('amount');
            $table->dropForeign(['country_id']);
            $table->dropColumn(['country_id']);
            $table->dropForeign(['city_id']);
            $table->dropColumn(['city_id']);
        });
    }
}
