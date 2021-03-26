<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTherapistWorkingSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('therapist_working_schedules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('date');
            $table->enum('is_working', ['0', '1'])->default('0')->comment("0: Nope, 1: Yes");
            $table->enum('is_absent', ['0', '1'])->nullable()->comment("0: Nope, 1: Yes");
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
        Schema::drop('therapist_working_schedules');
    }
}
