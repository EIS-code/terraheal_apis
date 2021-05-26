<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToTherapistSelectedTherapiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('therapist_selected_therapies', function (Blueprint $table) {
            DB::statement('DELETE FROM `therapist_selected_therapies`;');
            $table->bigInteger('therapy_id')->unsigned()->after('therapist_id');
            $table->foreign('therapy_id')->references('id')->on('therapies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('therapist_selected_therapies', function (Blueprint $table) {
            $table->dropForeign(['therapy_id']);
            $table->dropColumn(['therapy_id']);
        });
    }
}
