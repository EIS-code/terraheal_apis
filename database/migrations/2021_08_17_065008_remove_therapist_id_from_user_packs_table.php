<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveTherapistIdFromUserPacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_packs', function (Blueprint $table) {
            $table->dropForeign(['therapist_id']);
            $table->dropColumn(['therapist_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_packs', function (Blueprint $table) {
            $table->bigInteger('therapist_id')->unsigned();
            $table->foreign('therapist_id')->references('id')->on('therapists')->onDelete('cascade');
        });
    }
}
