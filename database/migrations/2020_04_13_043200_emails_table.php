<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EmailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('emails', function (Blueprint $table) {
            $table->id();
            $table->string('from', 50);
            $table->string('to')->comment('Comma separated email ids.');
            $table->string('cc')->nullable()->comment('Comma separated email ids.');
            $table->string('bcc')->nullable()->comment('Comma separated email ids.');
            $table->string('subject');
            $table->string('body')->nullable();
            $table->enum('is_send', [0, 1])->default(1)->comment('0: Not send, 1: Sent');
            $table->string('exception_info')->nullable();
            $table->dateTime('created_at', 0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('emails');
    }
}
