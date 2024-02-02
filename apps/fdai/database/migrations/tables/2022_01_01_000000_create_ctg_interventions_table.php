<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCtgInterventionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ctg_interventions', function (Blueprint $table) {
            $table->integer('id');
            $table->string('nct_id', 4369);
            $table->string('intervention_type', 4369);
            $table->string('name', 4369)->nullable();
            $table->text('description')->nullable();
            $table->integer('variable_id');
            $table->integer('var')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ctg_interventions');
    }
}
