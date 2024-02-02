<?php



namespace Database\Factories;

use App\Properties\Base\BaseValenceProperty;
use App\UI\IonIcon;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Variable;

class VariableFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'additional_meta_data' => [
                'some test key' => 'some test value'
            ],
            'brand_name' => $this->faker->sentence(2),
            'cause_only' => false,
            //'combination_operation' => VariableCombinationOperationProperty::COMBINATION_MEAN,
            'common_alias' => "Display name for test variable",
            'default_unit_id' => \App\Units\OneToFiveRatingUnit::ID,
            'description' => $this->faker->sentence(4),
            'image_url' => $this->faker->imageUrl(),
            'informational_url' => $this->faker->url,
            'ion_icon' => IonIcon::ion_icon_charts,
            'manual_tracking' => true,
            'name' => "Test Variable from Factory",
            'synonyms' => ["Test Synonym 1", "test Synonym 2"],
            'valence' => BaseValenceProperty::VALENCE_NEGATIVE,
            'variable_category_id' => \App\VariableCategories\EmotionsVariableCategory::ID,
            'wikipedia_url' => $this->faker->url,
            Variable::FIELD_IS_PUBLIC => 1,
            //'analysis_ended_at' => $this->faker->date('Y-m-d H:i:s'),
            //'analysis_requested_at' => $this->faker->date('Y-m-d H:i:s'),
            //'analysis_settings_modified_at' => $this->faker->date('Y-m-d H:i:s'),
            //'analysis_started_at' => $this->faker->date('Y-m-d H:i:s'),
            //'average_seconds_between_measurements' => $this->faker->randomDigitNotNull,
            //'best_cause_variable_id' => BupropionSrCommonVariable::ID,
            //'best_effect_variable_id' => OverallMoodCommonVariable::ID,
            //'charts' => null,
            //'common_maximum_allowed_daily_value' => $this->faker->randomDigitNotNull,
            //'common_minimum_allowed_daily_value' => $this->faker->randomDigitNotNull,
            //'common_minimum_allowed_non_zero_value' => $this->faker->randomDigitNotNull,
            //'creator_user_id' => UserIdProperty::USER_ID_TEST_USER,
            //'data_sources_count' => [],
            //'deleted_at' => null,
            //'earliest_non_tagged_measurement_start_at' => $this->faker->date('Y-m-d H:i:s'),
            //'earliest_tagged_measurement_start_at' => $this->faker->date('Y-m-d H:i:s'),
            //'internal_error_message' => $this->faker->word,
            //'kurtosis' => $this->faker->randomDigitNotNull,
            //'latest_non_tagged_measurement_start_at' => $this->faker->date('Y-m-d H:i:s'),
            //'latest_tagged_measurement_start_at' => $this->faker->date('Y-m-d H:i:s'),
            //'maximum_recorded_value' => $this->faker->randomDigitNotNull,
            //'mean' => $this->faker->randomDigitNotNull,
            //'median' => $this->faker->randomDigitNotNull,
            //'median_seconds_between_measurements' => $this->faker->randomDigitNotNull,
            //'minimum_allowed_seconds_between_measurements' => $this->faker->randomDigitNotNull,
            //'minimum_allowed_value' => $this->faker->randomDigitNotNull,
            //'minimum_recorded_value' => $this->faker->randomDigitNotNull,
            //'most_common_connector_id' => \App\DataSources\Connectors\FitbitConnector::ID,
            //'most_common_original_unit_id' => $this->faker->randomDigitNotNull,
            //'most_common_source_name' => FitbitConnector::NAME,
            //'most_common_value' => $this->faker->randomDigitNotNull,
            //'newest_data_at' => $this->faker->date('Y-m-d H:i:s'),
            //'number_of_global_variable_relationships_as_cause' => $this->faker->randomDigitNotNull,
            //'number_of_global_variable_relationships_as_effect' => $this->faker->randomDigitNotNull,
            //'number_of_measurements' => $this->faker->randomDigitNotNull,
            //'number_of_soft_deleted_measurements' => $this->faker->randomDigitNotNull,
            //'number_of_unique_values' => $this->faker->randomDigitNotNull,
            //'onset_delay' => 3600,
            //'optimal_value_message' => $this->faker->sentence(6),
            //'outcome' => $this->faker->word,
            //'parent_id' => null,
            //'price' => $this->faker->randomDigitNotNull,
            //'product_url' => $this->faker->url,
            //'reason_for_analysis' => $this->faker->word,
            //'second_most_common_value' => $this->faker->randomDigitNotNull,
            //'skewness' => $this->faker->randomDigitNotNull,
            //'standard_deviation' => $this->faker->randomDigitNotNull,
            //'status' => VariableStatusProperty::STATUS_UPDATED,
            //'third_most_common_value' => $this->faker->randomDigitNotNull,
            //'upc_12' => $this->faker->word,
            //'upc_14' => $this->faker->word,
            //'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            //'user_error_message' => $this->faker->word,
            //'wikipedia_title' => $this->faker->word,
            //'wp_post_id' => null,
            //'best_global_variable_relationship_id' => $this->faker->randomDigitNotNull
            //'client_id' => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT,
            //'created_at' => $this->faker->date('Y-m-d H:i:s'),
            //'default_value' => $this->faker->randomDigitNotNull,
            //'filling_value' => $this->faker->randomDigitNotNull,
            //'maximum_allowed_value' => $this->faker->randomDigitNotNull,
            //'number_common_tagged_by' => $this->faker->randomDigitNotNull,
            //'number_of_common_tags' => $this->faker->randomDigitNotNull,
            //'number_of_raw_measurements_with_tags_joins_children' => $this->faker->randomDigitNotNull,
            //'number_of_tracking_reminders' => $this->faker->randomDigitNotNull,
            //'number_of_user_variables' => 0,
            //'user_id' => $this->faker->randomDigitNotNull,
            //'variance' => $this->faker->randomDigitNotNull,
        ];
    }
}
