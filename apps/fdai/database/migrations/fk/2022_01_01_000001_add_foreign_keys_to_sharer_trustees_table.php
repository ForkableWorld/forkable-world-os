<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToSharerTrusteesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sharer_trustees', function (Blueprint $table) {
            $table->foreign(['sharer_user_id'], 'sharer_trustees_wp_users_ID_fk')->references(['ID'])->deferrable()->on('wp_users');
            $table->foreign(['trustee_user_id'], 'sharer_trustees_wp_users_ID_fk_2')->references(['ID'])->deferrable()->on('wp_users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sharer_trustees', function (Blueprint $table) {
            $table->dropForeign('sharer_trustees_wp_users_ID_fk');
            $table->dropForeign('sharer_trustees_wp_users_ID_fk_2');
        });
    }
}
