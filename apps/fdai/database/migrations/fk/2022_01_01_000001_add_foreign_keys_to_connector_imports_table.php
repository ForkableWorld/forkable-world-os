<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToConnectorImportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('connector_imports', function (Blueprint $table) {
            $table->foreign(['client_id'], 'connector_imports_client_id_fk')->references(['client_id'])->on('oa_clients');
            $table->foreign(['connection_id'], 'connector_imports_connections_id_fk')->references(['id'])->deferrable()->on('connections');
            $table->foreign(['connector_id'], 'connector_imports_connectors_id_fk')->references(['id'])->deferrable()->on('connectors');
            $table->foreign(['user_id'], 'connector_imports_wp_users_ID_fk')->references(['ID'])->deferrable()->on('wp_users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('connector_imports', function (Blueprint $table) {
            $table->dropForeign('connector_imports_client_id_fk');
            $table->dropForeign('connector_imports_connections_id_fk');
            $table->dropForeign('connector_imports_connectors_id_fk');
            $table->dropForeign('connector_imports_wp_users_ID_fk');
        });
    }
}
