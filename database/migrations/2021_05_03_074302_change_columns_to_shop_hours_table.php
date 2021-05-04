<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnsToShopHoursTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shop_hours', function (Blueprint $table) {
            $table->dropColumn('sunday');
            $table->dropColumn('monday');
            $table->dropColumn('tuesday');
            $table->dropColumn('wednesday');
            $table->dropColumn('thursday');
            $table->dropColumn('friday');
            $table->dropColumn('saturday');
            $table->enum('day_name', [0, 1, 2, 3, 4, 5, 6])->comment('0: Sunday, 1: Monday, 2: Tuesday, 3: Wednesday, 4: Thursday, 5: Friday, 6: Saturday')->after('id');
            $table->enum('is_open',[0,1])->comment('0: No, 1: Yes')->default(0)->after('day_name');
            $table->time('open_at')->nullable()->after('is_open');
            $table->time('close_at')->nullable()->after('open_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shop_hours', function (Blueprint $table) {
            $table->dropColumn('day_name');
            $table->dropColumn('is_open');
            $table->dropColumn('open_at');
            $table->dropColumn('close_at');
            $table->enum('sunday', ['0', '1'])->default('0')->comment('0: Nope, 1: Yes');
            $table->enum('monday', ['0', '1'])->default('0')->comment('0: Nope, 1: Yes');
            $table->enum('tuesday', ['0', '1'])->default('0')->comment('0: Nope, 1: Yes');
            $table->enum('wednesday', ['0', '1'])->default('0')->comment('0: Nope, 1: Yes');
            $table->enum('thursday', ['0', '1'])->default('0')->comment('0: Nope, 1: Yes');
            $table->enum('friday', ['0', '1'])->default('0')->comment('0: Nope, 1: Yes');
            $table->enum('saturday', ['0', '1'])->default('0')->comment('0: Nope, 1: Yes');
        });
    }
}
