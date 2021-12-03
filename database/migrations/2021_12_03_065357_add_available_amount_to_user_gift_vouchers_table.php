<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAvailableAmountToUserGiftVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_gift_vouchers', function (Blueprint $table) {
            $table->float('available_amount')->after('amount')->nullable();
            $table->enum('is_used',[0,1])->after('available_amount')->comment('0: No, 1: Yes')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_gift_vouchers', function (Blueprint $table) {
            $table->dropColumn('available_amount');
            $table->dropColumn('is_used');
        });
    }
}
