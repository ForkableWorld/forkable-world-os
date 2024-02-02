<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToWpPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wp_posts', function (Blueprint $table) {
            $table->foreign(['post_author'], 'wp_posts_wp_users_ID_fk')
                  ->references(['ID'])
                  ->deferrable()
                  ->on('wp_users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wp_posts', function (Blueprint $table) {
            $table->dropForeign('wp_posts_wp_users_ID_fk');
        });
    }
}
