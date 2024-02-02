<?php

use App\Logging\ConsoleLog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToWpUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wp_users', function (Blueprint $table) {
//            $table->foreign(['primary_outcome_variable_id'], 'wp_users_variables_id_fk')->references(['id'])->deferrable()->on('variables');
//            $table->foreign(['wp_post_id'], 'wp_users_wp_posts_ID_fk')->references(['ID'])->deferrable()->on('wp_posts')->onUpdate('CASCADE')->onDelete('SET NULL');
//            $table->foreign(['referrer_user_id'], 'wp_users_wp_users_ID_fk')->references(['ID'])->deferrable()->on('wp_users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wp_users', function (Blueprint $table) {
            $table->dropForeign('wp_users_variables_id_fk');
            $table->dropForeign('wp_users_wp_posts_ID_fk');
            $table->dropForeign('wp_users_wp_users_ID_fk');
        });
    }
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function tryTable($table, $callback)
    {
        try {
            Schema::table($table, $callback);
        } catch (\Throwable $e) {
            ConsoleLog::error(__METHOD__.": ".$e->getMessage());
        }
    }
}
