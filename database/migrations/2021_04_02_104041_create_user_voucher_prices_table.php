<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserVoucherPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_voucher_prices', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('voucher_id')->unsigned();
            $table->foreign('voucher_id')->references('id')->on('vouchers')->onDelete('cascade');
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->float('total_value');
            $table->float('used_value')->nullable()->default(0.00);
            $table->float('available_value')->nullable()->default(0.00);
            $table->date('purchase_date');
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
        Schema::dropIfExists('user_voucher_prices');
    }
}
