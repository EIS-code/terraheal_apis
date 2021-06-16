<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddServicesToUserPackMassagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_pack_massages', function (Blueprint $table) {
            $table->bigInteger('service_price_id')->unsigned()->after('is_removed');
            $table->foreign('service_price_id')->references('id')->on('service_pricings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_pack_massages', function (Blueprint $table) {
            $table->dropForeign(['service_price_id']);
            $table->dropColumn(['service_price_id']);
        });
    }
}
