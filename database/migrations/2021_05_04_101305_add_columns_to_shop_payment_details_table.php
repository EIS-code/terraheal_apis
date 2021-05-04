<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToShopPaymentDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shop_payment_details', function (Blueprint $table) {
            $table->string('sales_percentage')->nullable()->after('apple_pay_number');
            $table->string('inital_amount')->nullable()->after('sales_percentage');
            $table->string('fixed_amount')->nullable()->after('inital_amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shop_payment_details', function (Blueprint $table) {
            $table->dropColumn('sales_percentage');
            $table->dropColumn('inital_amount');
            $table->dropColumn('fixed_amount');
            
        });
    }
}
