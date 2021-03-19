<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSourceToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            
            $table->text('client_note')->nullable();
            $table->enum('source', [0, 1, 2, 3, 4, 5])->nullable()
                    ->comment('0: Internet, 1: Recommendation, 2: Flyer, 3: Publicity, 4: Hotel, 5: By Chance');
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
            
            $table->dropColumn('client_note');
            $table->dropColumn('source');
            
        });
    }
}
