<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToUserVariableClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_variable_clients', function (Blueprint $table) {
            $table->foreign(['client_id'], 'user_variable_clients_client_id_fk')->references(['client_id'])->on('oa_clients');
            $table->foreign(['user_id'], 'user_variable_clients_user_id_fk')->references(['ID'])->deferrable()->on('wp_users');
            $table->foreign(['user_variable_id'], 'user_variable_clients_user_variables_user_variable_id_fk')->references(['id'])->deferrable()->on('user_variables');
            $table->foreign(['variable_id'], 'user_variable_clients_variable_id_fk')->references(['id'])->deferrable()->on('variables');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_variable_clients', function (Blueprint $table) {
            $table->dropForeign('user_variable_clients_client_id_fk');
            $table->dropForeign('user_variable_clients_user_id_fk');
            $table->dropForeign('user_variable_clients_user_variables_user_variable_id_fk');
            $table->dropForeign('user_variable_clients_variable_id_fk');
        });
    }
}
