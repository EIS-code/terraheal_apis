<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserIdToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_email_unique');
            $table->bigInteger('user_id')->unsigned()->nullable()->after('shop_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('user_gender_preference_id')->unsigned()->nullable()->after('user_id');
            $table->foreign('user_gender_preference_id')->references('id')->on('user_gender_preferences')->onDelete('cascade');
            $table->string('email')->unique()->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id']);
            $table->dropForeign(['user_gender_preference_id']);
            $table->dropColumn(['user_gender_preference_id']);
            $table->string('email')->unique()->nullable(false)->change();
        });
    }
}
