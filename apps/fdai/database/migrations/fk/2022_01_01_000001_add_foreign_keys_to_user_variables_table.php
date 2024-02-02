<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToUserVariablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_variables', function (Blueprint $table) {
            $table->foreign(['client_id'], 'user_variables_client_id_fk')->references(['client_id'])->on('oa_clients');
            $table->foreign(['best_user_variable_relationship_id'], 'user_variables_correlations_qm_score_fk')->references(['id'])->deferrable()->on('user_variable_relationships')->onDelete('SET NULL');
            $table->foreign(['default_unit_id'], 'user_variables_default_unit_id_fk')->references(['id'])->deferrable()->on('units');
            $table->foreign(['last_unit_id'], 'user_variables_last_unit_id_fk')->references(['id'])->deferrable()->on('units');
            $table->foreign(['user_id'], 'user_variables_user_id_fk')->references(['ID'])->deferrable()->on('wp_users');
            $table->foreign(['variable_id'], 'user_variables_variables_id_fk')->references(['id'])->deferrable()->on('variables');
            $table->foreign(['variable_category_id'], 'user_variables_variable_category_id_fk')->references(['id'])->deferrable()->on('variable_categories');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_variables', function (Blueprint $table) {
            $table->dropForeign('user_variables_client_id_fk');
            $table->dropForeign('user_variables_correlations_qm_score_fk');
            $table->dropForeign('user_variables_default_unit_id_fk');
            $table->dropForeign('user_variables_last_unit_id_fk');
            $table->dropForeign('user_variables_user_id_fk');
            $table->dropForeign('user_variables_variables_id_fk');
            $table->dropForeign('user_variables_variable_category_id_fk');
        });
    }
}
