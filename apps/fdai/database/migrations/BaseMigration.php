<?php

use App\Logging\ConsoleLog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BaseMigration extends Migration
{
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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
