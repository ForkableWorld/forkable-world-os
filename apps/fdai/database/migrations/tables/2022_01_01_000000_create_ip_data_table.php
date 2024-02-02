<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIpDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('ip_data', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->softDeletes();
            $table->timestamp('updated_at')->useCurrent()->nullable();
            $table->string('ip')->unique('ip_data_ip_uindex');
            $table->string('hostname')->nullable();
            $table->string('type')->nullable();
            $table->string('continent_code')->nullable();
            $table->string('continent_name')->nullable();
            $table->string('country_code')->nullable();
            $table->string('country_name')->nullable();
            $table->string('region_code')->nullable();
            $table->string('region_name')->nullable();
            $table->string('city')->nullable();
            $table->string('zip')->nullable();
            $table->float('latitude', 0, 0)->nullable();
            $table->float('longitude', 0, 0)->nullable();
            $table->text('location')->nullable();
            $table->text('time_zone')->nullable();
            $table->text('currency')->nullable();
            $table->text('connection')->nullable();
            $table->text('security')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ip_data');
    }
}
