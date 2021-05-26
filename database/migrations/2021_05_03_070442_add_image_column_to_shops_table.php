<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImageColumnToShopsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('open_time');
            $table->dropColumn('close_time');
            $table->dropColumn('open_day_from');
            $table->dropColumn('open_day_to');
            $table->string('owner_mobile_number_alternative', 50)->nullable()->after('owner_mobile_number');
            $table->string('owner_surname')->nullable()->after('owner_name');
            $table->string('finacial_situation')->nullable()->after('owner_email');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('owner_mobile_number_alternative');
            $table->dropColumn('owner_surname');
            $table->dropColumn('finacial_situation');
            $table->time('open_time')->nullable();
            $table->time('close_time')->nullable();
            $table->enum('open_day_from', [0, 1, 2, 3, 4, 5, 6])->comment('0: Monday, 1: Tuesday, 2: Wednesday, 3: Thursday, 4: Friday, 5: Saturday, 6: Sunday')->nullable();
            $table->enum('open_day_to', [0, 1, 2, 3, 4, 5, 6])->comment('0: Monday, 1: Tuesday, 2: Wednesday, 3: Thursday, 4: Friday, 5: Saturday, 6: Sunday')->nullable();
        });
    }
}
