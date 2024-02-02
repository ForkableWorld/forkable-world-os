<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCtConditionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('ct_conditions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100)->unique('conName');
            $table->integer('variable_id')->unique('ct_conditions_variable_id_uindex');
            $table->timestamp('updated_at')->useCurrent();
            $table->timestamp('created_at')->useCurrent();
            $table->softDeletes();
            $table->integer('number_of_treatments');
            $table->integer('number_of_symptoms')->nullable();
            $table->integer('number_of_causes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ct_conditions');
    }
}
