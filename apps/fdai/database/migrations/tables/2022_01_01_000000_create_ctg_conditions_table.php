<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCtgConditionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ctg_conditions', function (Blueprint $table) {
            $table->integer('id');
            $table->string('nct_id', 4369);
            $table->string('name', 4369);
            $table->string('downcase_name', 4369);
            $table->bigInteger('variable_id');
        });
    }

}
