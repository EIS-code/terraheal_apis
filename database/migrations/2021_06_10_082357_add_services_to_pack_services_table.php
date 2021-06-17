<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddServicesToPackServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pack_services', function (Blueprint $table) {
            $table->bigInteger('service_id')->unsigned()->after('pack_id')->nullable();
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            $table->bigInteger('service_timing_id')->unsigned()->after('service_id')->nullable();
            $table->foreign('service_timing_id')->references('id')->on('service_timings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pack_services', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->dropColumn(['service_id']);
            $table->dropForeign(['service_timing_id']);
            $table->dropColumn(['service_timing_id']);
        });
    }
}
