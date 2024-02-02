<?php


namespace Database\Factories;

use App\Properties\UserVariableRelationship\CorrelationConfidenceLevelProperty;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\OAClient;
use App\Models\UserVariableRelationship;
use App\Properties\UserVariableRelationship\CorrelationStatusProperty;
use App\Properties\User\UserIdProperty;
use App\VariableCategories\EmotionsVariableCategory;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use App\Variables\CommonVariables\TreatmentsCommonVariables\BupropionSrCommonVariable;

class CorrelationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $userId = UserIdProperty::USER_ID_TEST_USER;
        $cause = BupropionSrCommonVariable::instance();
        $causeUserVariable = $cause->getOrCreateUserVariable($userId);
        $effect = OverallMoodCommonVariable::instance();
        $effectUserVariable = $effect->getOrCreateUserVariable($userId);
        //$exampleValues = UserVariableRelationship::getExampleValues();
        //return $exampleValues;
        return [
            'global_variable_relationship_id' => null,
            'aggregated_at' => $this->faker->date('Y-m-d H:i:s'),
            'analysis_ended_at' => $this->faker->date('Y-m-d H:i:s'),
            'analysis_requested_at' => $this->faker->date('Y-m-d H:i:s'),
            'analysis_started_at' => $this->faker->date('Y-m-d H:i:s'),
            'average_daily_high_cause' => 5,
            'average_daily_low_cause' => 1,
            'average_effect' => $this->faker->randomDigitNotNull,
            'average_effect_following_high_cause' => 5,
            'average_effect_following_low_cause' => 1,
            'average_forward_pearson_correlation_over_onset_delays' => 0,
            'average_reverse_pearson_correlation_over_onset_delays' => 0,
            'cause_baseline_average_per_day' => $this->faker->randomDigitNotNull,
            'cause_baseline_average_per_duration_of_action' => $this->faker->randomDigitNotNull,
            'cause_changes' => $this->faker->randomDigitNotNull,
            'cause_filling_value' => $this->faker->randomDigitNotNull,
            'cause_number_of_processed_daily_measurements' => 6,
            'cause_number_of_raw_measurements' => $this->faker->randomDigitNotNull,
            'cause_treatment_average_per_day' => $this->faker->randomDigitNotNull,
            'cause_treatment_average_per_duration_of_action' => $this->faker->randomDigitNotNull,
            'cause_unit_id' => $causeUserVariable->getCommonUnitId(),
            'cause_user_variable_id' => $causeUserVariable->getUserVariableId(),
            'cause_variable_category_id' => EmotionsVariableCategory::ID,
            'cause_variable_id' => $causeUserVariable->getVariableIdAttribute(),
            'charts' => null,
            'client_id' => OAClient::first()->client_id,
            'confidence_interval' => $this->faker->randomDigitNotNull,
            'confidence_level' => CorrelationConfidenceLevelProperty::CONFIDENCE_LEVEL_HIGH,
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'critical_t_value' => $this->faker->randomDigitNotNull,
            'data_source_name' => $this->faker->word,
            'deleted_at' => null,
            UserVariableRelationship::FIELD_DURATION_OF_ACTION => 86400,
            'earliest_measurement_start_at' => $this->faker->date('Y-m-d H:i:s'),
            'effect_baseline_average' => $this->faker->randomDigitNotNull,
            'effect_baseline_relative_standard_deviation' => $this->faker->randomDigitNotNull,
            'effect_baseline_standard_deviation' => $this->faker->randomDigitNotNull,
            'effect_changes' => $this->faker->randomDigitNotNull,
            'effect_filling_value' => null,
            'effect_follow_up_average' => $this->faker->randomDigitNotNull,
            'effect_follow_up_percent_change_from_baseline' => $this->faker->randomDigitNotNull,
            'effect_number_of_processed_daily_measurements' => 10,
            'effect_number_of_raw_measurements' => $this->faker->randomDigitNotNull,
            'effect_user_variable_id' => $effectUserVariable->getUserVariableId(),
            'effect_variable_category_id' => $effectUserVariable->getQMVariableCategory()->id,
            'effect_variable_id' => $effectUserVariable->getVariableIdAttribute(),
            'experiment_end_at' => $this->faker->date('Y-m-d H:i:s'),
            'experiment_start_at' => $this->faker->date('Y-m-d H:i:s'),
            'forward_pearson_correlation_coefficient' => $this->faker->randomDigitNotNull,
            'forward_spearman_correlation_coefficient' => $this->faker->randomDigitNotNull,
            'grouped_cause_value_closest_to_value_predicting_high_outcome' => $this->faker->randomDigitNotNull,
            'grouped_cause_value_closest_to_value_predicting_low_outcome' => $this->faker->randomDigitNotNull,
            'interesting_variable_category_pair' => true,
            'internal_error_message' => $this->faker->word,
            'latest_measurement_start_at' => $this->faker->date('Y-m-d H:i:s'),
            'newest_data_at' => $this->faker->date('Y-m-d H:i:s'),
            'number_of_days' => 10,
            'number_of_pairs' => 11,
            'onset_delay' => 3600,
            'onset_delay_with_strongest_pearson_correlation' => $this->faker->randomDigitNotNull,
            'optimal_pearson_product' => $this->faker->randomDigitNotNull,
            'p_value' => $this->faker->randomDigitNotNull,
            'pearson_correlation_with_no_onset_delay' => $this->faker->randomDigitNotNull,
            'predictive_pearson_correlation_coefficient' => $this->faker->randomDigitNotNull,
            'predicts_high_effect_change' => $this->faker->randomDigitNotNull,
            'predicts_low_effect_change' => $this->faker->randomDigitNotNull,
            'published_at' => $this->faker->date('Y-m-d H:i:s'),
            'qm_score' => $this->faker->randomDigitNotNull,
            'reason_for_analysis' => $this->faker->word,
            'reverse_pearson_correlation_coefficient' => $this->faker->randomDigitNotNull,
            'statistical_significance' => $this->faker->randomDigitNotNull,
            'status' => CorrelationStatusProperty::STATUS_WAITING,
            'strongest_pearson_correlation_coefficient' => $this->faker->randomDigitNotNull,
            't_value' => $this->faker->randomDigitNotNull,
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'user_error_message' => $this->faker->word,
            'user_id' => UserIdProperty::USER_ID_TEST_USER,
            'value_predicting_high_outcome' => $this->faker->randomDigitNotNull,
            'value_predicting_low_outcome' => $this->faker->randomDigitNotNull,
            'wp_post_id' => null,
            'z_score' => $this->faker->randomDigitNotNull,
        ];
    }
}
