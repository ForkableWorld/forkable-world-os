<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToCtgConditionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            Schema::table('ctg_conditions', function (Blueprint $table) {
                $table->foreign(['variable_id'], 'ctg_conditions_variables_id_fk')->references(['id'])->deferrable()->on('variables');
            });
        } catch (\Throwable $e) {
            if(!str_contains($e->getMessage(), 'Duplicate')){
                throw $e;
            }
        }

    }

}
