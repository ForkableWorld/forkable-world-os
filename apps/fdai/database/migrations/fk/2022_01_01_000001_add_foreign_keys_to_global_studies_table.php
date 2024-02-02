<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToGlobalStudiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('global_studies', function (Blueprint $table) {
            $table->foreign(['global_variable_relationship_id'], 'global_studies_global_variable_relationships_id_fk')->references(['id'])->deferrable()->on('global_variable_relationships');
            $table->foreign(['cause_variable_id'], 'global_studies_cause_variable_id_variables_id_fk')->references(['id'])->deferrable()->on('variables');
            $table->foreign(['client_id'], 'global_studies_client_id_fk')->references(['client_id'])->on('oa_clients');
            $table->foreign(['effect_variable_id'], 'global_studies_effect_variable_id_variables_id_fk')->references(['id'])->deferrable()->on('variables');
            $table->foreign(['user_id'], 'global_studies_user_id_fk')->references(['ID'])->deferrable()->on('wp_users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('global_studies', function (Blueprint $table) {
            $table->dropForeign('global_studies_global_variable_relationships_id_fk');
            $table->dropForeign('global_studies_cause_variable_id_variables_id_fk');
            $table->dropForeign('global_studies_client_id_fk');
            $table->dropForeign('global_studies_effect_variable_id_variables_id_fk');
            $table->dropForeign('global_studies_user_id_fk');
        });
    }
}
