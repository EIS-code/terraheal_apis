<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTherapiesPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('therapies_prices', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('therapy_id')->unsigned();
            $table->bigInteger('therapy_timing_id')->unsigned()->unique();
            $table->foreign('therapy_id')->references('id')->on('therapies')->onDelete('cascade');
            $table->foreign('therapy_timing_id')->references('id')->on('therapies_timings')->onDelete('cascade');
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
        Schema::dropIfExists('therapies_prices');
    }
}
