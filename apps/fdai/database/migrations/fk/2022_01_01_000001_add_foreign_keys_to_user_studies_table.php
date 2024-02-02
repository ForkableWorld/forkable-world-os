<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToUserStudiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_studies', function (Blueprint $table) {
            $table->foreign(['cause_user_variable_id'], 'user_studies_cause_user_variables_id_fk')->references(['id'])->deferrable()->on('user_variables');
            $table->foreign(['cause_variable_id'], 'user_studies_cause_variables_id_fk')->references(['id'])->deferrable()->on('variables');
            $table->foreign(['correlation_id'], 'user_studies_correlations_id_fk')->references(['id'])->deferrable()->on('user_variable_relationships');
            $table->foreign(['effect_user_variable_id'], 'user_studies_effect_user_variables_id_fk')->references(['id'])->deferrable()->on('user_variables');
            $table->foreign(['effect_variable_id'], 'user_studies_effect_variables_id_fk')->references(['id'])->deferrable()->on('variables');
            $table->foreign(['user_id'], 'user_studies_user_id_fk')->references(['ID'])->deferrable()->on('wp_users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_studies', function (Blueprint $table) {
            $table->dropForeign('user_studies_cause_user_variables_id_fk');
            $table->dropForeign('user_studies_cause_variables_id_fk');
            $table->dropForeign('user_studies_correlations_id_fk');
            $table->dropForeign('user_studies_effect_user_variables_id_fk');
            $table->dropForeign('user_studies_effect_variables_id_fk');
            $table->dropForeign('user_studies_user_id_fk');
        });
    }
}
