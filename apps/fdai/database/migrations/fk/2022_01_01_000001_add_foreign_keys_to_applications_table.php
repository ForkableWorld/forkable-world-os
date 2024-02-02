<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->foreign(['client_id'], 'applications_client_id_fk')->references(['client_id'])->on('oa_clients');
            $table->foreign(['outcome_variable_id'], 'applications_outcome_variable_id_fk')->references(['id'])->deferrable()->on('variables');
            $table->foreign(['predictor_variable_id'], 'applications_predictor_variable_id_fk')->references(['id'])->deferrable()->on('variables');
            $table->foreign(['user_id'], 'applications_user_id_fk')->references(['ID'])->deferrable()->on('wp_users');
            $table->foreign(['wp_post_id'], 'applications_wp_posts_ID_fk')->references(['ID'])->deferrable()->on('wp_posts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropForeign('applications_client_id_fk');
            $table->dropForeign('applications_outcome_variable_id_fk');
            $table->dropForeign('applications_predictor_variable_id_fk');
            $table->dropForeign('applications_user_id_fk');
            $table->dropForeign('applications_wp_posts_ID_fk');
        });
    }
}
