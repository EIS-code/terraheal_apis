<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToReceptionistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('receptionists', function (Blueprint $table) {
            $table->dropColumn('whatsapp_number');
            $table->dropColumn('upload_id');
            $table->dropColumn('insurance');
            $table->string('dob')->nullable()->after('tel_number');
            $table->enum('gender', ['m', 'f'])->comment('m: Male, f: Female')->after('dob');
            $table->string('emergency_tel_number', 50)->nullable()->after('gender');
            $table->string('nif')->nullable()->after('emergency_tel_number');
            $table->string('security_number')->nullable()->after('nif');
            $table->text('address')->nullable()->after('security_number');
            $table->bigInteger('country_id')->unsigned()->nullable()->after('shop_id');
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
        Schema::table('receptionists', function (Blueprint $table) {
            $table->string('whatsapp_number', 50)->nullable();
            $table->string('upload_id')->nullable();
            $table->string('insurance')->nullable();
            $table->dropColumn('dob');
            $table->dropColumn('gender');
            $table->dropColumn('emergency_tel_number');
            $table->dropColumn('nif');
            $table->dropColumn('security_number');
            $table->dropColumn('address');
            $table->dropForeign(['country_id']);
            $table->dropColumn('country_id');
            $table->dropForeign(['city_id']);
            $table->dropColumn('city_id');
        });
    }
}
