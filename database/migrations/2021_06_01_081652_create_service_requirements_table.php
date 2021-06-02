<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceRequirementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_requirements', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('service_id')->unsigned();
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            $table->string('massage_through');
            $table->string('special_tools');
            $table->enum('platform',[0, 1, 2])->comment('0: Massage table, 1: Tatami/Futon, 2: Both');
            $table->enum('oil_usage',[0, 1, 2])->comment('0: Use oil, 1: Use just a bit of oil, 2: Dry massage');
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
        Schema::dropIfExists('service_requirements');
    }
}
