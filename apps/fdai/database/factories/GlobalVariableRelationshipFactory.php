<?php



namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\GlobalVariableRelationship;
use App\Models\OAClient;
use App\Properties\GlobalVariableRelationship\GlobalVariableRelationshipConfidenceLevelProperty;
use App\Units\MilligramsUnit;
use App\VariableCategories\EmotionsVariableCategory;
use App\VariableCategories\TreatmentsVariableCategory;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use App\Variables\CommonVariables\TreatmentsCommonVariables\BupropionSrCommonVariable;

class GlobalVariableRelationshipFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'aggregate_qm_score' => $this->faker->randomDigitNotNull,
            'analysis_ended_at' => $this->faker->date('Y-m-d H:i:s'),
            'analysis_requested_at' => $this->faker->date('Y-m-d H:i:s'),
            'analysis_started_at' => $this->faker->date('Y-m-d H:i:s'),
            'average_daily_high_cause' => $this->faker->randomDigitNotNull,
            'average_daily_low_cause' => $this->faker->randomDigitNotNull,
            'average_effect' => $this->faker->randomDigitNotNull,
            'average_effect_following_high_cause' => $this->faker->randomDigitNotNull,
            'average_effect_following_low_cause' => $this->faker->randomDigitNotNull,
            'average_vote' => $this->faker->randomDigitNotNull,
            'cause_baseline_average_per_day' => $this->faker->randomDigitNotNull,
            'cause_baseline_average_per_duration_of_action' => $this->faker->randomDigitNotNull,
            'cause_changes' => $this->faker->randomDigitNotNull,
            'cause_treatment_average_per_day' => $this->faker->randomDigitNotNull,
            'cause_treatment_average_per_duration_of_action' => $this->faker->randomDigitNotNull,
            'cause_unit_id' => MilligramsUnit::ID,
            'cause_variable_category_id' => TreatmentsVariableCategory::ID,
            'cause_variable_id' => BupropionSrCommonVariable::ID,
            'charts' => $this->faker->text,
            'client_id' => OAClient::first()->client_id,
            'confidence_interval' => $this->faker->randomDigitNotNull,
            'confidence_level' => GlobalVariableRelationshipConfidenceLevelProperty::CONFIDENCE_LEVEL_HIGH,
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'critical_t_value' => $this->faker->randomDigitNotNull,
            'data_source_name' => $this->faker->word,
            'deleted_at' => null,
            'effect_baseline_average' => $this->faker->randomDigitNotNull,
            'effect_baseline_relative_standard_deviation' => $this->faker->randomDigitNotNull,
            'effect_baseline_standard_deviation' => $this->faker->randomDigitNotNull,
            'effect_changes' => $this->faker->randomDigitNotNull,
            'effect_follow_up_average' => $this->faker->randomDigitNotNull,
            'effect_follow_up_percent_change_from_baseline' => $this->faker->randomDigitNotNull,
            'effect_variable_category_id' => EmotionsVariableCategory::ID,
            'effect_variable_id' => OverallMoodCommonVariable::ID,
            'forward_pearson_correlation_coefficient' => $this->faker->randomDigitNotNull,
            'grouped_cause_value_closest_to_value_predicting_high_outcome' => $this->faker->randomDigitNotNull,
            'grouped_cause_value_closest_to_value_predicting_low_outcome' => $this->faker->randomDigitNotNull,
            'interesting_variable_category_pair' => true,
            'internal_error_message' => null,
            'newest_data_at' => $this->faker->date('Y-m-d H:i:s'),
            'number_of_correlations' => $this->faker->randomDigitNotNull,
            'number_of_pairs' => $this->faker->randomDigitNotNull,
            'number_of_users' => $this->faker->randomDigitNotNull,
            'onset_delay' => 3600,
            'optimal_pearson_product' => $this->faker->randomDigitNotNull,
            'p_value' => $this->faker->randomDigitNotNull,
            'population_trait_pearson_correlation_coefficient' => $this->faker->randomDigitNotNull,
            'predictive_pearson_correlation_coefficient' => $this->faker->randomDigitNotNull,
            'predicts_high_effect_change' => $this->faker->randomDigitNotNull,
            'predicts_low_effect_change' => $this->faker->randomDigitNotNull,
            'published_at' => $this->faker->date('Y-m-d H:i:s'),
            'reason_for_analysis' => $this->faker->word,
            'reverse_pearson_correlation_coefficient' => $this->faker->randomDigitNotNull,
            'statistical_significance' => $this->faker->randomDigitNotNull,
            'status' => $this->faker->word,
            't_value' => $this->faker->randomDigitNotNull,
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'user_error_message' => null,
            'value_predicting_high_outcome' => $this->faker->randomDigitNotNull,
            'value_predicting_low_outcome' => $this->faker->randomDigitNotNull,
            'wp_post_id' => null,
            'z_score' => $this->faker->randomDigitNotNull,
        ];
    }
}
