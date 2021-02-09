<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->enum('rating', [1, 2, 3, 4, 5])->comment('1: Very Bad, 2: Bad, 3: Medium, 4: Good One, 5: Too Happy');
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            // $table->bigInteger('shop_id')->unsigned();
            // $table->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade');
            $table->enum('is_delete', [0, 1])->default(0)->comment('0: Nope, 1: Deleted');
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
        Schema::dropIfExists('reviews');
    }
}
