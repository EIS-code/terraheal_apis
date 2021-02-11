<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ApiKeysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->enum('type', ['0', '1', '2', '3', '4'])->default('0')->comment('0: None, 1: Users, 2: Therapists, 3: Freelancer Therapists, 4: Shops');
            $table->bigInteger('model_id')->unsigned();
            $table->bigInteger('api_key_id')->unsigned();
            $table->foreign('api_key_id')->references('id')->on('api_key_shops')->onDelete('cascade');
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
        Schema::dropIfExists('api_keys');
    }
}
