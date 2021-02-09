<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TherapistEmailOtpsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('therapist_email_otps', function (Blueprint $table) {
            $table->id();
            $table->string('otp');
            $table->string('email');
            $table->enum('is_send', ['0', '1'])->default('0')->comment('0: Nope, 1: Yes');
            $table->enum('is_verified', ['0', '1'])->default('0')->comment('0: Nope, 1: Yes');
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
        Schema::dropIfExists('therapist_email_otps');
    }
}
