<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToWpUsermetaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wp_usermeta', function (Blueprint $table) {
            $table->foreign(['user_id'], 'wp_usermeta_wp_users_ID_fk')->references(['ID'])->deferrable()->on('wp_users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wp_usermeta', function (Blueprint $table) {
            $table->dropForeign('wp_usermeta_wp_users_ID_fk');
        });
    }
}
