<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UserGiftVoucherThemeDesignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_gift_voucher_theme_designs', function (Blueprint $table) {
            $table->id();
            $table->string('image');
            $table->bigInteger('theme_id')->unsigned();
            $table->foreign('theme_id')->references('id')->on('user_gift_voucher_themes')->onDelete('cascade');
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
        Schema::dropIfExists('user_gift_voucher_theme_designs');
    }
}
