<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToDeviceTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('device_tokens', function (Blueprint $table) {
            $table->foreign(['client_id'], 'device_tokens_client_id_fk')->references(['client_id'])->on('oa_clients');
            $table->foreign(['user_id'], 'device_tokens_user_id_fk')->references(['ID'])->deferrable()->on('wp_users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('device_tokens', function (Blueprint $table) {
            $table->dropForeign('device_tokens_client_id_fk');
            $table->dropForeign('device_tokens_user_id_fk');
        });
    }
}
