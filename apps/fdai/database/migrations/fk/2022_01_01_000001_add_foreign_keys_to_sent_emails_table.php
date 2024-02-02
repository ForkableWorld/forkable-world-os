<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToSentEmailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sent_emails', function (Blueprint $table) {
            $table->foreign(['client_id'], 'sent_emails_client_id_fk')->references(['client_id'])->on('oa_clients');
            $table->foreign(['user_id'], 'sent_emails_user_id_fk')->references(['ID'])->deferrable()->on('wp_users');
            $table->foreign(['wp_post_id'], 'sent_emails_wp_posts_ID_fk')->references(['ID'])->deferrable()->on('wp_posts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sent_emails', function (Blueprint $table) {
            $table->dropForeign('sent_emails_client_id_fk');
            $table->dropForeign('sent_emails_user_id_fk');
            $table->dropForeign('sent_emails_wp_posts_ID_fk');
        });
    }
}
