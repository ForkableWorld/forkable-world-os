<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVariableOutcomeCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('variable_outcome_category', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('variable_id');
            $table->smallInteger('variable_category_id')->index('v_outcome_category_variable_categories_id_fk');
            $table->integer('number_of_outcome_variables');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->softDeletes();

            $table->unique(['variable_id', 'variable_category_id'], 'variable_outcome_category_uindex');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('variable_outcome_category');
    }
}
