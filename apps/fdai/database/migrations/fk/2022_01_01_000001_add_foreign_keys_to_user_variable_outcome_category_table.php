<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToUserVariableOutcomeCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_variable_outcome_category', function (Blueprint $table) {
            $table->foreign(['user_variable_id'], 'user_variable_outcome_category_user_variables_id_fk')->references(['id'])->deferrable()->on('user_variables');
            $table->foreign(['variable_id'], 'user_variable_outcome_category_variables_id_fk')->references(['id'])->deferrable()->on('variables');
            $table->foreign(['variable_category_id'], 'user_variable_outcome_category_variable_categories_id_fk')->references(['id'])->deferrable()->on('variable_categories');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_variable_outcome_category', function (Blueprint $table) {
            $table->dropForeign('user_variable_outcome_category_user_variables_id_fk');
            $table->dropForeign('user_variable_outcome_category_variables_id_fk');
            $table->dropForeign('user_variable_outcome_category_variable_categories_id_fk');
        });
    }
}
