<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateThirdPartyCorrelationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('third_party_correlations', function (Blueprint $table) {
            $table->integer('cause_id');
            $table->integer('effect_id');
            $table->float('qm_score', 0, 0)->nullable();
            $table->float('forward_pearson_correlation_coefficient', 0, 0)->nullable();
            $table->float('value_predicting_high_outcome', 0, 0)->nullable();
            $table->float('value_predicting_low_outcome', 0, 0)->nullable();
            $table->integer('predicts_high_effect_change')->nullable();
            $table->integer('predicts_low_effect_change')->nullable();
            $table->float('average_effect', 0, 0)->nullable();
            $table->float('average_effect_following_high_cause', 0, 0)->nullable();
            $table->float('average_effect_following_low_cause', 0, 0)->nullable();
            $table->float('average_daily_low_cause', 0, 0)->nullable();
            $table->float('average_daily_high_cause', 0, 0)->nullable();
            $table->float('average_forward_pearson_correlation_over_onset_delays', 0, 0)->nullable();
            $table->float('average_reverse_pearson_correlation_over_onset_delays', 0, 0)->nullable();
            $table->integer('cause_changes')->nullable();
            $table->float('cause_filling_value', 0, 0)->nullable();
            $table->integer('cause_number_of_processed_daily_measurements');
            $table->integer('cause_number_of_raw_measurements');
            $table->integer('cause_unit_id')->nullable();
            $table->float('confidence_interval', 0, 0)->nullable();
            $table->float('critical_t_value', 0, 0)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->string('data_source_name')->nullable();
            $table->softDeletes();
            $table->integer('duration_of_action')->nullable();
            $table->integer('effect_changes')->nullable();
            $table->float('effect_filling_value', 0, 0)->nullable();
            $table->integer('effect_number_of_processed_daily_measurements');
            $table->integer('effect_number_of_raw_measurements');
            $table->text('error')->nullable();
            $table->float('forward_spearman_correlation_coefficient', 0, 0)->nullable();
            $table->increments('id');
            $table->integer('number_of_days');
            $table->integer('number_of_pairs')->nullable();
            $table->integer('onset_delay')->nullable();
            $table->integer('onset_delay_with_strongest_pearson_correlation')->nullable();
            $table->float('optimal_pearson_product', 0, 0)->nullable();
            $table->float('p_value', 0, 0)->nullable();
            $table->float('pearson_correlation_with_no_onset_delay', 0, 0)->nullable();
            $table->float('predictive_pearson_correlation_coefficient', 0, 0)->nullable();
            $table->float('reverse_pearson_correlation_coefficient', 0, 0)->nullable();
            $table->float('statistical_significance', 0, 0)->nullable();
            $table->float('strongest_pearson_correlation_coefficient', 0, 0)->nullable();
            $table->float('t_value', 0, 0)->nullable();
            $table->timestamp('updated_at')->useCurrent();
            $table->bigInteger('user_id');
            $table->float('grouped_cause_value_closest_to_value_predicting_low_outcome', 0, 0)->nullable();
            $table->float('grouped_cause_value_closest_to_value_predicting_high_outcome', 0, 0)->nullable();
            $table->string('client_id');
            $table->timestamp('published_at')->nullable();
            $table->integer('wp_post_id')->nullable();
            $table->string('status', 25)->nullable();
            $table->smallInteger('cause_variable_category_id');
            $table->smallInteger('effect_variable_category_id');
            $table->boolean('interesting_variable_category_pair')->nullable();
            $table->integer('cause_variable_id')->nullable();
            $table->integer('effect_variable_id')->nullable();

            $table->unique(['user_id', 'cause_id', 'effect_id'], 'tpc_user_cause_effect');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('third_party_correlations');
    }
}
