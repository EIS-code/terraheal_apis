<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToTherapistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('therapists', function (Blueprint $table) {
            $table->boolean('surname')->nullable()->after('name');
            $table->boolean('account_number')->nullable()->after('is_document_verified');
            $table->boolean('nif')->nullable()->after('account_number');
            $table->boolean('social_security_number')->nullable()->after('nif');
            $table->boolean('mobile_number')->nullable()->after('tel_number');
            $table->boolean('emergence_contact_number')->nullable()->after('mobile_number');
            $table->bigInteger('city_id')->unsigned();
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade')->after('shop_id');
            $table->bigInteger('country_id')->unsigned();
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade')->after('city_id');
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
            $table->dropColumn('surname');
            $table->dropColumn('account_number');
            $table->dropColumn('nif');
            $table->dropColumn('social_security_number');
            $table->dropColumn('mobile_number');
            $table->dropColumn('emergence_contact_number');
            $table->dropColumn('city_id');
            $table->dropColumn('country_id');
        });
    }
}
