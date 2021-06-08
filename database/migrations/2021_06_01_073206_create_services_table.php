<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('english_name');
            $table->string('portugese_name')->nullable();
            $table->string('short_description')->nullable();
            $table->string('priority')->nullable();
            $table->string('expenses')->nullable();
            $table->enum('service_type',[0, 1])->comment('0: Massage, 1: Therapy')->default(0);
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
        Schema::dropIfExists('services');
    }
}
