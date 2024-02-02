<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToCtConditionTreatmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ct_condition_treatment', function (Blueprint $table) {
            $table->foreign(['condition_id'], 'ct_condition_treatment_conditions_id_fk')->references(['id'])->deferrable()->on('ct_conditions');
            $table->foreign(['treatment_id'], 'ct_condition_treatment_ct_treatments_fk')->references(['id'])->deferrable()->on('ct_treatments');
            $table->foreign(['treatment_variable_id'], 'ct_condition_treatment_variables_id_fk')->references(['id'])->deferrable()->on('variables');
            $table->foreign(['condition_variable_id'], 'ct_condition_treatment_variables_id_fk_2')->references(['id'])->deferrable()->on('variables');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ct_condition_treatment', function (Blueprint $table) {
            $table->dropForeign('ct_condition_treatment_conditions_id_fk');
            $table->dropForeign('ct_condition_treatment_ct_treatments_fk');
            $table->dropForeign('ct_condition_treatment_variables_id_fk');
            $table->dropForeign('ct_condition_treatment_variables_id_fk_2');
        });
    }
}
