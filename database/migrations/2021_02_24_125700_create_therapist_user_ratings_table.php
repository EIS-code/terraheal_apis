<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTherapistUserRatingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('therapist_user_ratings', function (Blueprint $table) {
            $table->id();
            $table->enum('rating', ['0', '1','2','3','4','5'])->default('0')->comment('0: None, 1: Very Bad, 2: Bad, 3: Medium, 4: Good One, 5: Too Happy');
            $table->enum('type', ['0','1','2','3','4','5'])->default('0')->comment('0: None, 0: Punctuality And Presence For Reservations, 1: Behavior, 2: Sexual Issues, 3: Hygiene, 4: Left Bad / Good Review, 5: Payment Issues');
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('therapist_user_ratings');
    }
}
