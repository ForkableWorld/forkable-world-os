<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('connections', function (Blueprint $table) {
            $table->string('connector_user_id')->nullable();
			$table->string('connector_user_email')->nullable();
			$table->index(['connector_user_id', 'connector_id'], 'connector_user_id_uindex');
        });
    }
};
