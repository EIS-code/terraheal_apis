<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ApiKeyShopsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('api_key_shops', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->bigInteger('shop_id')->unsigned()->nullable();
            $table->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade');
            $table->bigInteger('superadmin_id')->unsigned()->nullable();
            $table->foreign('superadmin_id')->references('id')->on('superadmins')->onDelete('cascade');
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
        Schema::dropIfExists('api_key_shops');
    }
}
