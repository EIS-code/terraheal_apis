<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddManagerIdToNewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn('is_read');
            $table->bigInteger('manager_id')->unsigned()->nullable()->after('description');
            $table->foreign('manager_id')->references('id')->on('manager')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('news', function (Blueprint $table) {
            $table->enum('is_read', ['0', '1'])->default('0')->comment('0: Nope, 1: Yes');
            $table->dropForeign(['manager_id']);
            $table->dropColumn(['manager_id']);
        });
    }
}
