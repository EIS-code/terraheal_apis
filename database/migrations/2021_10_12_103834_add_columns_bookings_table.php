<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            
            $table->float('total_price')->nullable()->after('pack_id');
            $table->float('remaining_price')->nullable()->after('total_price');
            $table->enum('payment_type', [0,1])->comment('0: Full, 1: Half')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            
            $table->dropColumn('total_price');
            $table->dropColumn('payment_type');
        });
    }
}
