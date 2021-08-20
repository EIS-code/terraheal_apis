<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveUaerPackIdFromUserPackGiftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_pack_gifts', function (Blueprint $table) {
//            $table->dropForeign(['user_pack_id']);
//            $table->dropColumn(['user_pack_id']);
            $table->bigInteger('pack_id')->unsigned();
            $table->foreign('pack_id')->references('id')->on('packs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_pack_gifts', function (Blueprint $table) {
            $table->dropForeign(['pack_id']);
            $table->dropColumn(['pack_id']);
            $table->bigInteger('user_pack_id')->unsigned();
            $table->foreign('user_pack_id')->references('id')->on('user_packs')->onDelete('cascade');
        });
    }
}
