<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToOaAuthorizationCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('oa_authorization_codes', function (Blueprint $table) {
            $table->foreign(['client_id'], 'oa_authorization_codes_client_id_fk')->references(['client_id'])->on('oa_clients');
            $table->foreign(['user_id'], 'oa_authorization_codes_user_id_fk')->references(['ID'])->deferrable()->on('wp_users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('oa_authorization_codes', function (Blueprint $table) {
            $table->dropForeign('oa_authorization_codes_client_id_fk');
            $table->dropForeign('oa_authorization_codes_user_id_fk');
        });
    }
}
