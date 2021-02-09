<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ShopsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('surname')->nullable();
            $table->string('address')->nullable();
            $table->string('address2')->nullable();
            $table->text('description')->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->decimal('latitude', 11, 8)->nullable();
            $table->integer('zoom')->nullable();
            $table->string('owner_name')->nullable();
            $table->string('tel_number', 50)->nullable();
            $table->string('owner_mobile_number', 50)->nullable();
            $table->string('owner_mobile_number_alternative', 50)->nullable();
            $table->string('owner_email')->nullable()->unique();
            $table->string('email')->unique();
            $table->string('time_zone')->nullable();
            $table->time('open_time')->nullable();
            $table->time('close_time')->nullable();
            $table->enum('open_day_from', [0, 1, 2, 3, 4, 5, 6])->comment('0: Monday, 1: Tuesday, 2: Wednesday, 3: Thursday, 4: Friday, 5: Saturday, 6: Sunday')->nullable();
            $table->enum('open_day_to', [0, 1, 2, 3, 4, 5, 6])->comment('0: Monday, 1: Tuesday, 2: Wednesday, 3: Thursday, 4: Friday, 5: Saturday, 6: Sunday')->nullable();
            $table->string('shop_user_name');
            $table->string('manager_user_name');
            $table->string('shop_password');
            $table->string('manager_password');
            $table->text('financial_situation')->nullable();
            $table->bigInteger('city_id')->unsigned()->nullable();
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');
            $table->bigInteger('province_id')->unsigned()->nullable();
            $table->foreign('province_id')->references('id')->on('provinces')->onDelete('cascade');
            $table->bigInteger('country_id')->unsigned()->nullable();
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
            $table->bigInteger('currency_id')->unsigned()->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade');
            $table->string('pin_code')->nullable();
            $table->string('featured_image')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('shops');
    }
}
