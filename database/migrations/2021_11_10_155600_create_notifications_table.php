<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->text('message')->nullable();
            $table->text('payload')->nullable();
            $table->text('device_token');
            $table->enum('is_success', ['0', '1'])->default('1')->comment('0: Nope, 1: Yes');
            $table->string('apns_id')->nullable();
            $table->text('error_infos')->nullable();
            $table->enum('send_to', ['0', '1', '2', '3', '4', '5', '6'])->default('0')->comment('0: None, 1: Superadmin, 2: Client APP, 3: Manager APP, 4: Manager EXE, 5: Shop APP, 6: Shop EXE');
            $table->enum('send_from', ['0', '1', '2', '3', '4', '5', '6'])->default('0')->comment('0: None, 1: Superadmin, 2: Client APP, 3: Manager APP, 4: Manager EXE, 5: Shop APP, 6: Shop EXE');
            $table->enum('is_read', ['0', '1'])->default('0')->comment('0: Nope, 1: Yes');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notifications');
    }
}
