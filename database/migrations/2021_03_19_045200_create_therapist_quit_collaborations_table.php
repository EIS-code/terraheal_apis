<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTherapistQuitCollaborationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('therapist_quit_collaborations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('reason')->nullable();
            $table->bigInteger('therapist_id')->unsigned();
            $table->foreign('therapist_id')->references('id')->on('therapists')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('therapist_quit_collaborations');
    }
}
