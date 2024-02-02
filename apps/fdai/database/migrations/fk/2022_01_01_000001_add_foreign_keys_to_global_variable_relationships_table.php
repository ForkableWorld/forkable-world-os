<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToGlobalVariableRelationshipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('global_variable_relationships', function (Blueprint $table) {
            $table->foreign(['cause_unit_id'], 'global_variable_relationships_cause_unit_id_fk')->references(['id'])->deferrable()->on('units');
            $table->foreign(['cause_variable_id'], 'global_variable_relationships_cause_variables_id_fk')->references(['id'])->deferrable()->on('variables');
            $table->foreign(['cause_variable_category_id'], 'global_variable_relationships_cause_variable_category_id_fk')->references(['id'])->deferrable()->on('variable_categories');
            $table->foreign(['client_id'], 'global_variable_relationships_client_id_fk')->references(['client_id'])->on('oa_clients');
            $table->foreign(['effect_variable_id'], 'global_variable_relationships_effect_variables_id_fk')->references(['id'])->deferrable()->on('variables');
            $table->foreign(['effect_variable_category_id'], 'global_variable_relationships_effect_variable_category_id_fk')->references(['id'])->deferrable()->on('variable_categories');
            $table->foreign(['wp_post_id'], 'global_variable_relationships_wp_posts_ID_fk')->references(['ID'])->deferrable()->on('wp_posts')->onUpdate('CASCADE')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('global_variable_relationships', function (Blueprint $table) {
            $table->dropForeign('global_variable_relationships_cause_unit_id_fk');
            $table->dropForeign('global_variable_relationships_cause_variables_id_fk');
            $table->dropForeign('global_variable_relationships_cause_variable_category_id_fk');
            $table->dropForeign('global_variable_relationships_client_id_fk');
            $table->dropForeign('global_variable_relationships_effect_variables_id_fk');
            $table->dropForeign('global_variable_relationships_effect_variable_category_id_fk');
            $table->dropForeign('global_variable_relationships_wp_posts_ID_fk');
        });
    }
}
