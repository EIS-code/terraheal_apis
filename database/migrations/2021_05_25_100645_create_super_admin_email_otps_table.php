<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuperAdminEmailOtpsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('super_admin_email_otps', function (Blueprint $table) {
            $table->id();
            $table->string('otp');
            $table->string('email');
            $table->enum('is_send', ['0', '1'])->default('0')->comment('0: Nope, 1: Yes');
            $table->enum('is_verified', ['0', '1'])->default('0')->comment('0: Nope, 1: Yes');
            $table->bigInteger('admin_id')->unsigned();
            $table->foreign('admin_id')->references('id')->on('superadmins')->onDelete('cascade');
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
        Schema::dropIfExists('super_admin_email_otps');
    }
}
