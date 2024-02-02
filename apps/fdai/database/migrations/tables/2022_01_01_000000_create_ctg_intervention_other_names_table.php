<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCtgInterventionOtherNamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ctg_intervention_other_names', function (Blueprint $table) {
            $table->integer('id');
            $table->string('nct_id', 4369);
            $table->integer('intervention_id');
            $table->string('name', 4369);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ctg_intervention_other_names');
    }
}
