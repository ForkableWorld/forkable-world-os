<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserVariablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('user_variables', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('parent_id')->nullable()->comment('ID of the parent variable if this variable has any parent');
            $table->string('client_id', 80)->nullable()->index('user_variables_client_id_fk');
            $table->bigInteger('user_id')->index('user_variables_user_id_latest_tagged_measurement_time_index');
            $table->integer('variable_id')->index('fk_variableSettings')->comment('ID of variable');
            $table->smallInteger('default_unit_id')->nullable()->index('user_variables_default_unit_id_fk')->comment('ID of unit to use for this variable');
            $table->float('minimum_allowed_value', 0, 0)->nullable()->comment('Minimum reasonable value for this variable (uses default unit)');
            $table->float('maximum_allowed_value', 0, 0)->nullable()->comment('Maximum reasonable value for this variable (uses default unit)');
            $table->float('filling_value', 0, 0)->nullable()->default(-1)->comment('Value for replacing null measurements');
            $table->integer('join_with')->nullable()->comment('The Variable this Variable should be joined with. If the variable is joined with some other variable then it is not shown to user in the list of variables');
            $table->integer('onset_delay')->nullable();
            $table->integer('duration_of_action')->nullable()->comment('Estimated duration of time following the onset delay in which a stimulus produces a perceivable effect');
            $table->smallInteger('variable_category_id')->nullable()->index('user_variables_variable_category_id_fk')->comment('ID of variable category');
            $table->boolean('cause_only')->nullable()->comment('A value of 1 indicates that this variable is generally a cause in a causal relationship.  An example of a causeOnly variable would be a variable such as Cloud Cover which would generally not be influenced by the behaviour of the user');
            $table->enum('filling_type', ['value', 'none'])->nullable()->comment('0 -> No filling, 1 -> Use filling-value');
            $table->integer('number_of_processed_daily_measurements')->nullable()->comment('Number of processed measurements');
            $table->integer('measurements_at_last_analysis')->default(0);
            $table->smallInteger('last_unit_id')->nullable();
            $table->smallInteger('last_original_unit_id')->nullable()->comment('ID of last original Unit');
            $table->float('last_value', 0, 0)->nullable();
            $table->float('last_original_value', 0, 0)->nullable();
            $table->integer('number_of_correlations')->nullable();
            $table->string('status', 25)->nullable();
            $table->float('standard_deviation', 0, 0)->nullable();
            $table->float('variance', 0, 0)->nullable();
            $table->float('minimum_recorded_value', 0, 0)->nullable();
            $table->float('maximum_recorded_value', 0, 0)->nullable();
            $table->float('mean', 0, 0)->nullable();
            $table->float('median', 0, 0)->nullable();
            $table->integer('most_common_original_unit_id')->nullable();
            $table->float('most_common_value', 0, 0)->nullable();
            $table->integer('number_of_unique_daily_values')->nullable();
            $table->integer('number_of_unique_values')->nullable();
            $table->integer('number_of_changes')->nullable();
            $table->float('skewness', 0, 0)->nullable();
            $table->float('kurtosis', 0, 0)->nullable();
            $table->float('latitude', 0, 0)->nullable();
            $table->float('longitude', 0, 0)->nullable();
            $table->string('location')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->boolean('outcome')->nullable();
            $table->text('data_sources_count')->nullable();
            $table->integer('earliest_filling_time')->nullable();
            $table->integer('latest_filling_time')->nullable();
            $table->float('last_processed_daily_value', 0, 0)->nullable();
            $table->boolean('outcome_of_interest')->nullable()->default(false);
            $table->boolean('predictor_of_interest')->nullable()->default(false);
            $table->timestamp('experiment_start_time')->nullable();
            $table->timestamp('experiment_end_time')->nullable();
            $table->text('description')->nullable();
            $table->softDeletes();
            $table->string('alias', 125)->nullable();
            $table->float('second_to_last_value', 0, 0)->nullable();
            $table->float('third_to_last_value', 0, 0)->nullable();
            $table->integer('number_of_user_variable_relationships_as_effect')->nullable();
            $table->integer('number_of_user_variable_relationships_as_cause')->nullable();
            $table->enum('combination_operation', ['SUM', 'MEAN'])->nullable();
            $table->string('informational_url', 2000)->nullable();
            $table->integer('most_common_connector_id')->nullable();
            $table->enum('valence', ['positive', 'negative', 'neutral', null])->nullable()->comment('Set the valence positive if more is better for all the variables in the category, negative if more is bad, and neutral if none of the variables have such a valence. Valence is null if there is not a consistent valence for all variables in the category. ');
	         $table->string('wikipedia_title', 100)->nullable();
            $table->integer('number_of_tracking_reminders');
            $table->integer('number_of_raw_measurements_with_tags_joins_children')->nullable();
            $table->string('most_common_source_name')->nullable();
            $table->string('optimal_value_message', 500)->nullable();
            $table->integer('best_cause_variable_id')->nullable();
            $table->integer('best_effect_variable_id')->nullable();
            $table->float('user_maximum_allowed_daily_value', 0, 0)->nullable();
            $table->float('user_minimum_allowed_daily_value', 0, 0)->nullable();
            $table->float('user_minimum_allowed_non_zero_value', 0, 0)->nullable();
            $table->integer('minimum_allowed_seconds_between_measurements')->nullable();
            $table->integer('average_seconds_between_measurements')->nullable();
            $table->integer('median_seconds_between_measurements')->nullable();
            $table->timestamp('last_correlated_at')->nullable();
            $table->integer('number_of_measurements_with_tags_at_last_correlation')->nullable();
            $table->timestamp('analysis_settings_modified_at')->nullable();
            $table->timestamp('newest_data_at')->nullable();
            $table->timestamp('analysis_requested_at')->nullable();
            $table->string('reason_for_analysis')->nullable();
            $table->timestamp('analysis_started_at')->nullable()->index();
            $table->timestamp('analysis_ended_at')->nullable();
            $table->text('user_error_message')->nullable();
            $table->text('internal_error_message')->nullable();
            $table->timestamp('earliest_source_measurement_start_at')->nullable();
            $table->timestamp('latest_source_measurement_start_at')->nullable();
            $table->timestamp('latest_tagged_measurement_start_at')->nullable();
            $table->timestamp('earliest_tagged_measurement_start_at')->nullable();
            $table->timestamp('latest_non_tagged_measurement_start_at')->nullable();
            $table->timestamp('earliest_non_tagged_measurement_start_at')->nullable();
            $table->bigInteger('wp_post_id')->nullable()->index('user_variables_wp_posts_ID_fk');
            $table->integer('number_of_soft_deleted_measurements')->nullable();
            $table->integer('best_user_variable_relationship_id')->nullable()->index('user_variables_correlations_qm_score_fk');
            $table->integer('number_of_measurements')->nullable();
            $table->integer('number_of_tracking_reminder_notifications')->nullable();
            $table->string('deletion_reason', 280)->nullable();
            $table->integer('record_size_in_kb')->nullable();
            $table->integer('number_of_common_tags')->nullable();
            $table->integer('number_common_tagged_by')->nullable();
            $table->integer('number_of_common_joined_variables')->nullable();
            $table->integer('number_of_common_ingredients')->nullable();
            $table->integer('number_of_common_foods')->nullable();
            $table->integer('number_of_common_children')->nullable();
            $table->integer('number_of_common_parents')->nullable();
            $table->integer('number_of_user_tags')->nullable();
            $table->integer('number_user_tagged_by')->nullable();
            $table->integer('number_of_user_joined_variables')->nullable();
            $table->integer('number_of_user_ingredients')->nullable();
            $table->integer('number_of_user_foods')->nullable();
            $table->integer('number_of_user_children')->nullable();
            $table->integer('number_of_user_parents')->nullable();
            $table->boolean('is_public')->nullable();
            $table->string('slug', 200)->nullable()->unique('user_variables_slug_uindex');
	         $table->enum('is_goal', ['ALWAYS', 'SOMETIMES', 'NEVER', NULL])
		         ->nullable()
	               ->comment('The effect of a food on the severity of a symptom is useful because you can control the predictor directly. However, the effect of a symptom on the foods you eat is not very useful.  The foods you eat are not generally an objective end in themselves. ');
	         $table->enum('controllable', ['ALWAYS', 'SOMETIMES', 'NEVER', NULL])
		         ->nullable()
	               ->comment('The effect of a food on the severity of a symptom is useful because you can control the predictor directly. However, the effect of a symptom on the foods you eat is not very useful.  Symptom severity is not directly controllable. ');
            $table->boolean('boring')->nullable();
            $table->boolean('predictor')->nullable();

            $table->unique(['user_id', 'variable_id'], 'uv_user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_variables');
    }
}
