<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsDefaultToUserCardDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_card_details', function (Blueprint $table) {
            $table->enum('is_default', [0,1])->default(0)->after('user_id')->comment('0: No, 1: Yes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_card_details', function (Blueprint $table) {
            $table->dropColumn('is_default');
        });
    }
}
