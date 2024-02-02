<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToMeasurementImportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('measurement_imports', function (Blueprint $table) {
            $table->foreign(['client_id'], 'measurement_imports_client_id_fk')->references(['client_id'])->on('oa_clients');
            $table->foreign(['user_id'], 'measurement_imports_user_id_fk')->references(['ID'])->deferrable()->on('wp_users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('measurement_imports', function (Blueprint $table) {
            $table->dropForeign('measurement_imports_client_id_fk');
            $table->dropForeign('measurement_imports_user_id_fk');
        });
    }
}
