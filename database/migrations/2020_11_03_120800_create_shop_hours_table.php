<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopHoursTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shop_hours', function (Blueprint $table) {
            $table->id();
            $table->enum('sunday', ['0', '1'])->default('0')->comment('0: Nope, 1: Yes');
            $table->enum('monday', ['0', '1'])->default('0')->comment('0: Nope, 1: Yes');
            $table->enum('tuesday', ['0', '1'])->default('0')->comment('0: Nope, 1: Yes');
            $table->enum('wednesday', ['0', '1'])->default('0')->comment('0: Nope, 1: Yes');
            $table->enum('thursday', ['0', '1'])->default('0')->comment('0: Nope, 1: Yes');
            $table->enum('friday', ['0', '1'])->default('0')->comment('0: Nope, 1: Yes');
            $table->enum('saturday', ['0', '1'])->default('0')->comment('0: Nope, 1: Yes');
            $table->bigInteger('shop_id')->unsigned();
            $table->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade');
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
        Schema::dropIfExists('shop_hours');
    }
}
