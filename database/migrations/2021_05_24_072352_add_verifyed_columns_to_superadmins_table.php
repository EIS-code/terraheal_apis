<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVerifyedColumnsToSuperadminsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('superadmins', function (Blueprint $table) {
            $table->enum('is_email_verified', ['0', '1'])->default('0')->comment('0: Nope, 1: Yes')->after('remember_token');
            $table->enum('is_mobile_verified', ['0', '1'])->default('0')->comment('0: Nope, 1: Yes')->after('is_email_verified');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('superadmins', function (Blueprint $table) {
            $table->dropColumn('is_email_verified');
            $table->dropColumn('is_mobile_verified');
        });
    }
}
