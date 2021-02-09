<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UserPackGiftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_pack_gifts', function (Blueprint $table) {
            $table->id();
            $table->string('recipient_name');
            $table->string('recipient_last_name');
            $table->string('recipient_second_name')->nullable();
            $table->string('recipient_mobile');
            $table->string('recipient_email');
            $table->string('giver_first_name');
            $table->string('giver_last_name');
            $table->string('giver_mobile');
            $table->string('giver_email');
            $table->text('giver_message_to_recipient');
            $table->string('preference_email');
            $table->timestamp('preference_email_date');
            $table->bigInteger('user_pack_id')->unsigned();
            $table->foreign('user_pack_id')->references('id')->on('user_packs')->onDelete('cascade');
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->enum('is_removed', ['0', '1'])->default('0')->comment('0: Nope, 1: Yes');
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
        Schema::dropIfExists('user_pack_gifts');
    }
}
