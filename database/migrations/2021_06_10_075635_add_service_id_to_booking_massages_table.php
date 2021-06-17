<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddServiceIdToBookingMassagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('booking_massages', function (Blueprint $table) {
            $table->bigInteger('service_pricing_id')->unsigned()->after('focus_area_preference')->nullable();
            $table->foreign('service_pricing_id')->references('id')->on('service_pricings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('booking_massages', function (Blueprint $table) {
            $table->dropForeign(['service_pricing_id']);
            $table->dropColumn(['service_pricing_id']);
        });
    }
}
