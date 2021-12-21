<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPlatformToUserPacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_packs', function (Blueprint $table) {
            $table->enum('purchase_platform',[0,1,2])->after('user_id')->comment('0: App, 1: Web, 2: Center')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_packs', function (Blueprint $table) {
            $table->dropColumn('purchase_platform');
        });
    }
}
