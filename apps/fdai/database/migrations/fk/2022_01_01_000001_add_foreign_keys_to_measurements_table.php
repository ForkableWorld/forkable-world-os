<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToMeasurementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('measurements', function (Blueprint $table) {
            $table->foreign(['client_id'], 'measurements_client_id_fk')->references(['client_id'])->on('oa_clients');
            $table->foreign(['connection_id'], 'measurements_connections_id_fk')->references(['id'])->deferrable()->on('connections');
            $table->foreign(['connector_id'], 'measurements_connectors_id_fk')->references(['id'])->deferrable()->on('connectors');
            $table->foreign(['connector_import_id'], 'measurements_connector_imports_id_fk')->references(['id'])->deferrable()->on('connector_imports');
            $table->foreign(['original_unit_id'], 'measurements_original_unit_id_fk')->references(['id'])->deferrable()->on('units');
            $table->foreign(['unit_id'], 'measurements_unit_id_fk')->references(['id'])->deferrable()->on('units');
            $table->foreign(['user_id'], 'measurements_user_id_fk')->references(['ID'])->deferrable()->on('wp_users');
            $table->foreign(['user_variable_id'], 'measurements_user_variables_user_variable_id_fk')->references(['id'])->deferrable()->on('user_variables');
            $table->foreign(['variable_id'], 'measurements_variables_id_fk')->references(['id'])->deferrable()->on('variables');
            $table->foreign(['variable_category_id'], 'measurements_variable_category_id_fk')->references(['id'])->deferrable()->on('variable_categories');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('measurements', function (Blueprint $table) {
            $table->dropForeign('measurements_client_id_fk');
            $table->dropForeign('measurements_connections_id_fk');
            $table->dropForeign('measurements_connectors_id_fk');
            $table->dropForeign('measurements_connector_imports_id_fk');
            $table->dropForeign('measurements_original_unit_id_fk');
            $table->dropForeign('measurements_unit_id_fk');
            $table->dropForeign('measurements_user_id_fk');
            $table->dropForeign('measurements_user_variables_user_variable_id_fk');
            $table->dropForeign('measurements_variables_id_fk');
            $table->dropForeign('measurements_variable_category_id_fk');
        });
    }
}
