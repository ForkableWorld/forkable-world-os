<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCtCorrelationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('ct_correlations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->float('correlation_coefficient', 0, 0)->nullable();
            $table->integer('cause_variable_id')->index('cause');
            $table->integer('effect_variable_id')->index('effect');
            $table->integer('onset_delay')->nullable();
            $table->integer('duration_of_action')->nullable();
            $table->integer('number_of_pairs')->nullable();
            $table->float('value_predicting_high_outcome', 0, 0)->nullable();
            $table->float('value_predicting_low_outcome', 0, 0)->nullable();
            $table->float('optimal_pearson_product', 0, 0)->nullable();
            $table->float('vote', 0, 0)->nullable()->default(0.5);
            $table->float('statistical_significance', 0, 0)->nullable();
            $table->integer('cause_unit_id')->nullable();
            $table->integer('cause_changes')->nullable();
            $table->integer('effect_changes')->nullable();
            $table->float('qm_score', 0, 0)->nullable();
            $table->text('error')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->softDeletes();

            $table->unique(['user_id', 'cause_variable_id', 'effect_variable_id'], 'ct_correlations_user');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ct_correlations');
    }
}
