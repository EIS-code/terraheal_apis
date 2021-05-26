<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToTherapistExchangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('therapist_exchanges', function (Blueprint $table) {
            $table->bigInteger('shift_id')->unsigned()->after('therapist_id');
            $table->foreign('shift_id')->references('id')->on('shop_shifts')->onDelete('cascade');
            $table->bigInteger('with_therapist_id')->unsigned()->after('shift_id');
            $table->foreign('with_therapist_id')->references('id')->on('therapists')->onDelete('cascade');
            $table->bigInteger('with_shift_id')->unsigned()->after('with_therapist_id');
            $table->foreign('with_shift_id')->references('id')->on('shop_shifts')->onDelete('cascade');
            $table->bigInteger('shop_id')->unsigned()->after('with_shift_id');
            $table->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('therapist_exchanges', function (Blueprint $table) {
            $table->dropForeign(['shift_id']);
            $table->dropColumn(['shift_id']);
            $table->dropForeign(['with_therapist_id']);
            $table->dropColumn(['with_therapist_id']);
            $table->dropForeign(['with_shift_id']);
            $table->dropColumn(['with_shift_id']);
            $table->dropForeign(['shop_id']);
            $table->dropColumn(['shop_id']);
        });
    }
}
