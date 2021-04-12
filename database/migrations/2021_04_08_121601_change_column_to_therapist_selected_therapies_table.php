<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnToTherapistSelectedTherapiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('therapist_selected_therapies', function (Blueprint $table) {
            
            $table->dropForeign(['therapy_id']);
            $table->dropColumn(['therapy_id']);
            $table->bigInteger('therapy_id')->unsigned();
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
            $table->bigInteger('therapy_id')->unsigned();
            $table->foreign('therapy_id')->references('id')->on('massages')->onDelete('cascade');
        });
    }
}
