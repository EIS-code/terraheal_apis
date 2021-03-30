<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToTherapistUserRatingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('therapist_user_ratings', function (Blueprint $table) {
            $table->dropForeign(['therapist_id']);
            $table->dropColumn(['therapist_id']);
            $table->integer('model_id')->after('user_id');
            $table->string('model')->after('model_id');
            $table->bigInteger('edit_by')->unsigned()->after('model')->nullable();
            $table->foreign('edit_by')->references('id')->on('shops')->onDelete('cascade')->after('image');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('therapist_user_ratings', function (Blueprint $table) {
            $table->bigInteger('therapist_id')->unsigned();
            $table->foreign('therapist_id')->references('id')->on('therapists')->onDelete('cascade');
            $table->dropColumn('model_id');
            $table->dropColumn('model');
            $table->dropForeign(['edit_by']);
            $table->dropColumn(['edit_by']);
        });
    }
}
