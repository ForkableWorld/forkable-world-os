<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToButtonClicksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('button_clicks', function (Blueprint $table) {
            $table->foreign(['client_id'], 'button_clicks_client_id_fk')->references(['client_id'])->on('oa_clients');
            $table->foreign(['user_id'], 'button_clicks_user_id_fk')->references(['ID'])->deferrable()->on('wp_users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('button_clicks', function (Blueprint $table) {
            $table->dropForeign('button_clicks_client_id_fk');
            $table->dropForeign('button_clicks_user_id_fk');
        });
    }
}
