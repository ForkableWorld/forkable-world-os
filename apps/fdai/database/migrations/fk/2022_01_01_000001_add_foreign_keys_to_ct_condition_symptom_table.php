<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToCtConditionSymptomTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ct_condition_symptom', function (Blueprint $table) {
            $table->foreign(['condition_id'], 'ct_condition_symptom_conditions_fk')->references(['id'])->deferrable()->on('ct_conditions');
            $table->foreign(['symptom_id'], 'ct_condition_symptom_symptoms_fk')->references(['id'])->deferrable()->on('ct_symptoms');
            $table->foreign(['condition_variable_id'], 'ct_condition_symptom_variables_condition_fk')->references(['id'])->deferrable()->on('variables');
            $table->foreign(['symptom_variable_id'], 'ct_condition_symptom_variables_symptom_fk')->references(['id'])->deferrable()->on('variables');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ct_condition_symptom', function (Blueprint $table) {
            $table->dropForeign('ct_condition_symptom_conditions_fk');
            $table->dropForeign('ct_condition_symptom_symptoms_fk');
            $table->dropForeign('ct_condition_symptom_variables_condition_fk');
            $table->dropForeign('ct_condition_symptom_variables_symptom_fk');
        });
    }
}
