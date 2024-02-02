<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToStudiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('studies', function (Blueprint $table) {
            $table->foreign(['cause_variable_id'], 'studies_cause_variable_id_variables_id_fk')->references(['id'])->deferrable()->on('variables');
            $table->foreign(['client_id'], 'studies_client_id_fk')->references(['client_id'])->on('oa_clients');
            $table->foreign(['effect_variable_id'], 'studies_effect_variable_id_variables_id_fk')->references(['id'])->deferrable()->on('variables');
            $table->foreign(['user_id'], 'studies_user_id_fk')->references(['ID'])->deferrable()->on('wp_users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('studies', function (Blueprint $table) {
            $table->dropForeign('studies_cause_variable_id_variables_id_fk');
            $table->dropForeign('studies_client_id_fk');
            $table->dropForeign('studies_effect_variable_id_variables_id_fk');
            $table->dropForeign('studies_user_id_fk');
        });
    }
}
