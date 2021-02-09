<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MassagePricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('massage_prices', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('massage_id')->unsigned();
            $table->bigInteger('massage_timing_id')->unsigned()->unique();
            $table->foreign('massage_id')->references('id')->on('massages')->onDelete('cascade');
            $table->foreign('massage_timing_id')->references('id')->on('massage_timings')->onDelete('cascade');
            $table->float('price');
            $table->float('cost');
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
        Schema::dropIfExists('massage_prices');
    }
}
