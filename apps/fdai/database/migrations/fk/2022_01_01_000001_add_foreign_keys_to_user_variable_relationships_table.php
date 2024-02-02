<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToCorrelationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_variable_relationships', function (Blueprint $table) {
            $table->foreign(['global_variable_relationship_id'], 'correlations_global_variable_relationships_id_fk')->references(['id'])->deferrable()->on('global_variable_relationships');
            $table->foreign(['cause_unit_id'], 'correlations_cause_unit_id_fk')->references(['id'])->deferrable()->on('units');
            $table->foreign(['cause_variable_category_id'], 'correlations_cause_variable_category_id_fk')->references(['id'])->deferrable()->on('variable_categories');
            $table->foreign(['cause_variable_id'], 'correlations_cause_variable_id_fk')->references(['id'])->deferrable()->on('variables');
            $table->foreign(['client_id'], 'correlations_client_id_fk')->references(['client_id'])->on('oa_clients');
            $table->foreign(['effect_variable_id'], 'correlations_effect_variable_id_fk')->references(['id'])->deferrable()->on('variables');
            $table->foreign(['user_id'], 'correlations_user_id_fk')->references(['ID'])->deferrable()->on('wp_users');
            $table->foreign(['cause_user_variable_id'], 'correlations_user_variables_cause_user_variable_id_fk')->references(['id'])->deferrable()->on('user_variables')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign(['effect_user_variable_id'], 'correlations_user_variables_effect_user_variable_id_fk')->references(['id'])->deferrable()->on('user_variables')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign(['wp_post_id'], 'correlations_wp_posts_ID_fk')->references(['ID'])->deferrable()->on('wp_posts')->onUpdate('CASCADE')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_variable_relationships', function (Blueprint $table) {
            $table->dropForeign('correlations_global_variable_relationships_id_fk');
            $table->dropForeign('correlations_cause_unit_id_fk');
            $table->dropForeign('correlations_cause_variable_category_id_fk');
            $table->dropForeign('correlations_cause_variable_id_fk');
            $table->dropForeign('correlations_client_id_fk');
            $table->dropForeign('correlations_effect_variable_id_fk');
            $table->dropForeign('correlations_user_id_fk');
            $table->dropForeign('correlations_user_variables_cause_user_variable_id_fk');
            $table->dropForeign('correlations_user_variables_effect_user_variable_id_fk');
            $table->dropForeign('correlations_wp_posts_ID_fk');
        });
    }
}
