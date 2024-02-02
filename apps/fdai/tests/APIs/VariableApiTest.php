<?php namespace Tests\APIs;

use App\Exceptions\UnauthorizedException;
use App\Properties\User\UserIdProperty;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\RecordsNotFoundException;
use Tests\UnitTestCase;
use Tests\ApiTestTrait;
use App\Models\Variable;

class VariableApiTest extends UnitTestCase
{
    use ApiTestTrait;

    public function test_variable_search()
    {
        $data = $this->getApiV6('variables/search', ['q' => 'mood']);
        $r = $this->getTestResponse();
        $r->assertStatus(200);
        $data = $this->getJsonResponseData();
        $this->assertArrayEquals(array (
	         0 =>
	             array (
	                 'charts' => 'https://testing.quantimo.do/variables/Overall_Mood',
	                 'title' => 'Overall Mood',
	                 'id' => 1398,
	                 'name' => 'Overall Mood',
	                 'subtitle' => '1 studies',
	                 'common_alias' => 'Overall Mood',
	                 'slug' => 'overall-mood',
	                 'synonyms' =>
	                     array (
	                         0 => 'Mood',
	                         1 => 'Overall Mood',
	                         2 => 'Happy',
	                         3 => 'Happiness',
	                     ),
	                 'string_id' => 'overall_mood',
	                 'additional_meta_data' => NULL,
	                 'global_variable_relationships_count' => NULL,
	                 'global_variable_relationships_where_cause_variable_count' => NULL,
	                 'global_variable_relationships_where_effect_variable_count' => NULL,
	                 'analysis_ended_at' => '2022-12-19T14:47:03.000000Z',
	                 'analysis_requested_at' => NULL,
	                 'analysis_settings_modified_at' => NULL,
	                 'analysis_started_at' => '2022-12-19T14:46:51.000000Z',
	                 'average_seconds_between_measurements' => 86400,
	                 'best_global_variable_relationship_id' => 1,
	                 'best_cause_variable_id' => 1276,
	                 'best_effect_variable_id' => NULL,
	                 'boring' => NULL,
	                 'brand_name' => NULL,
	                 'canonical_variable_id' => NULL,
	                 'cause_only' => false,
	                 'client_id' => NULL,
	                 'combination_operation' => 'MEAN',
	                 'common_maximum_allowed_daily_value' => NULL,
	                 'common_minimum_allowed_daily_value' => NULL,
	                 'common_minimum_allowed_non_zero_value' => NULL,
	                 'common_tagged_by_count' => NULL,
	                 'common_tags_count' => NULL,
	                 'common_tags_where_tag_variable_count' => NULL,
	                 'common_tags_where_tagged_variable_count' => NULL,
	                 'condition_causes_where_cause_count' => NULL,
	                 'condition_causes_where_condition_count' => NULL,
	                 'condition_treatments_count' => NULL,
	                 'condition_treatments_where_condition_count' => NULL,
	                 'condition_treatments_where_treatment_count' => NULL,
	                 'controllable' => NULL,
	                 'correlation_causality_votes_where_cause_variable_count' => NULL,
	                 'correlation_causality_votes_where_effect_variable_count' => NULL,
	                 'correlation_usefulness_votes_where_cause_variable_count' => NULL,
	                 'correlation_usefulness_votes_where_effect_variable_count' => NULL,
	                 'correlations_count' => NULL,
	                 'correlations_where_cause_variable' =>
	                     array (
	                     ),
	                 'correlations_where_cause_variable_count' => NULL,
	                 'correlations_where_effect_variable' =>
	                     array (
	                     ),
	                 'correlations_where_effect_variable_count' => NULL,
	                 'created_at' => '2020-01-01T00:00:00.000000Z',
	                 'ct_treatment_side_effects_where_side_effect_variable_count' => NULL,
	                 'ct_treatment_side_effects_where_treatment_variable_count' => NULL,
	                 'data_sources_count' =>
	                     array (
	                         'oauth_test_client' => 2,
	                     ),
	                 'default_unit_id' => 10,
	                 'default_unit_name' => '1 to 5 Rating',
	                 'default_value' => 3,
	                 'description' => 'Your mood is the way you are feeling at a particular time. If you are in a good mood, you feel cheerful. If you are in a bad mood, you feel angry and impatient. ... If someone is in a mood, the way they are behaving shows that they are feeling angry and impatient.',
	                 'duration_of_action' => 86400,
	                 'earliest_non_tagged_measurement_start_at' => '2019-09-03T00:00:00.000000Z',
	                 'earliest_tagged_measurement_start_at' => '2019-09-03T00:00:00.000000Z',
	                 'filling_type' => 'none',
	                 'filling_value' => NULL,
	                 'image_url' => 'https://static.quantimo.do/img/emoticon-set/png/happy-1.png',
	                 'individual_cause_studies_count' => NULL,
	                 'individual_effect_studies_count' => NULL,
	                 'informational_url' => NULL,
	                 'ion_icon' => 'ion-happy-outline',
	                 'is_goal' => NULL,
	                 'is_public' => 1,
	                 'kurtosis' => 0.99166666666667,
	                 'latest_non_tagged_measurement_start_at' => '2019-12-31T00:00:00.000000Z',
	                 'latest_tagged_measurement_start_at' => '2019-12-31T00:00:00.000000Z',
	                 'manual_tracking' => true,
	                 'maximum_allowed_daily_value' => NULL,
	                 'maximum_allowed_value' => 5,
	                 'maximum_recorded_value' => 5,
	                 'mean' => 3,
	                 'measurements_count' => NULL,
	                 'median' => 3,
	                 'median_seconds_between_measurements' => 86400,
	                 'meta_data' => NULL,
	                 'minimum_allowed_seconds_between_measurements' => 60,
	                 'minimum_allowed_value' => 1,
	                 'minimum_recorded_value' => 1,
	                 'most_common_connector_id' => NULL,
	                 'most_common_original_unit_id' => NULL,
	                 'most_common_source_name' => 'oauth_test_client',
	                 'most_common_value' => 5,
	                 'newest_data_at' => '2022-12-19T14:47:01.000000Z',
	                 'number_common_tagged_by' => 0,
	                 'number_of_global_variable_relationships_as_cause' => 0,
	                 'number_of_global_variable_relationships_as_effect' => 1,
	                 'number_of_applications_where_outcome_variable' => 0,
	                 'number_of_applications_where_predictor_variable' => 0,
	                 'number_of_common_children' => NULL,
	                 'number_of_common_foods' => NULL,
	                 'number_of_common_ingredients' => NULL,
	                 'number_of_common_joined_variables' => NULL,
	                 'number_of_common_parents' => NULL,
	                 'number_of_common_tags' => 0,
	                 'number_of_common_tags_where_tag_variable' => 0,
	                 'number_of_common_tags_where_tagged_variable' => 0,
	                 'number_of_measurements' => 120,
	                 'number_of_outcome_case_studies' => 0,
	                 'number_of_outcome_population_studies' => 0,
	                 'number_of_predictor_case_studies' => 2,
	                 'number_of_predictor_population_studies' => 1,
	                 'number_of_raw_measurements' => NULL,
	                 'number_of_raw_measurements_with_tags_joins_children' => 120,
	                 'number_of_soft_deleted_measurements' => 0,
	                 'number_of_studies_where_cause_variable' => 0,
	                 'number_of_studies_where_effect_variable' => 1,
	                 'number_of_tracking_reminder_notifications' => 0,
	                 'number_of_tracking_reminders' => 0,
	                 'number_of_unique_values' => 2,
	                 'number_of_user_children' => NULL,
	                 'number_of_user_foods' => NULL,
	                 'number_of_user_ingredients' => NULL,
	                 'number_of_user_joined_variables' => NULL,
	                 'number_of_user_parents' => NULL,
	                 'number_of_user_tags_where_tag_variable' => 0,
	                 'number_of_user_tags_where_tagged_variable' => 0,
	                 'number_of_user_variables' => 7,
	                 'number_of_users_where_primary_outcome_variable' => 2,
	                 'number_of_variables_where_best_cause_variable' => 0,
	                 'number_of_variables_where_best_effect_variable' => 1,
	                 'number_of_votes_where_cause_variable' => 0,
	                 'number_of_votes_where_effect_variable' => 0,
	                 'onset_delay' => 0,
	                 'optimal_value_message' => 'Higher Bupropion Sr Intake predicts moderately higher Overall Mood. Overall Mood was 90.4% higher following above average Bupropion Sr over the previous 21 days. ',
	                 'outcome' => true,
	                 'outcomes_count' => NULL,
	                 'parent_id' => NULL,
	                 'population_cause_studies_count' => NULL,
	                 'population_effect_studies_count' => NULL,
	                 'predictor' => NULL,
	                 'predictors_count' => NULL,
	                 'price' => NULL,
	                 'product_url' => NULL,
	                 'public_outcomes_count' => NULL,
	                 'public_predictors_count' => NULL,
	                 'reason_for_analysis' => 'testUpdateTestDB',
	                 'record_size_in_kb' => NULL,
	                 'second_most_common_value' => 1,
	                 'side_effect_variables' =>
	                     array (
	                     ),
	                 'side_effect_variables_count' => NULL,
	                 'side_effects_count' => NULL,
	                 'skewness' => 0,
	                 'sort_order' => 0,
	                 'source_url' => NULL,
	                 'standard_deviation' => 2.0083857810137,
	                 'studies_count' => NULL,
	                 'studies_where_cause_variable_count' => NULL,
	                 'studies_where_effect_variable_count' => NULL,
	                 'tags_count' => NULL,
	                 'third_most_common_value' => NULL,
	                 'third_party_correlations_count' => NULL,
	                 'tracking_reminder_notifications_count' => NULL,
	                 'tracking_reminders_count' => NULL,
	                 'treatment_side_effects_where_treatment_count' => NULL,
	                 'up_voted_public_outcomes_count' => NULL,
	                 'up_voted_public_predictors_count' => NULL,
	                 'upc_12' => NULL,
	                 'upc_14' => NULL,
	                 'updated_at' => '2022-12-19T14:47:03.000000Z',
	                 'user_error_message' => NULL,
	                 'user_tagged_by_count' => NULL,
	                 'user_tags_count' => NULL,
	                 'user_tags_where_tag_variable_count' => NULL,
	                 'user_tags_where_tagged_variable_count' => NULL,
	                 'user_variable_clients_count' => NULL,
	                 'user_variable_outcome_categories_count' => NULL,
	                 'user_variable_predictor_categories_count' => NULL,
	                 'user_variables_count' => NULL,
	                 'user_variables_excluding_test_users_count' => NULL,
	                 'users_count' => NULL,
	                 'users_where_primary_outcome_variable_count' => NULL,
	                 'valence' => 'positive',
	                 'variable_category_id' => 1,
	                 'variable_category_name' => 'Emotions',
	                 'variable_id' => 1398,
	                 'variable_outcome_categories_count' => NULL,
	                 'variable_predictor_categories_count' => NULL,
	                 'variable_user_sources_count' => NULL,
	                 'variables_count' => NULL,
	                 'variables_where_best_cause_variable' =>
	                     array (
	                     ),
	                 'variables_where_best_cause_variable_count' => NULL,
	                 'variables_where_best_effect_variable' =>
	                     array (
	                     ),
	                 'variables_where_best_effect_variable_count' => NULL,
	                 'variance' => 4.0336134453782,
	                 'votes_count' => NULL,
	                 'votes_where_cause_count' => NULL,
	                 'votes_where_cause_variable_count' => NULL,
	                 'votes_where_effect_count' => NULL,
	                 'votes_where_effect_variable_count' => NULL,
	                 'wikipedia_title' => NULL,
	                 'wikipedia_url' => NULL,
	             ),
	     ), $data, "", true);
    }
    public function test_create_variable()
    {
        $input = Variable::factory()->make()->toArray();
        Variable::forceDeleteWhereLike(Variable::FIELD_NAME, $input['name']);
        if(!$input){$input = $this->getFakeDataForClassBeingTested();}
        $this->actingAsUserId(UserIdProperty::USER_ID_TEST_USER);
        $path = $this->getV6BasePathForClassTested();
        $r = $this->json(
            'POST',
            $path, $input
        );
        $expected = $input;
        $expected['synonyms'] = [
            "Test Variable from Factory",
            "Test Synonym 1",
            "test Synonym 2",
            "Display name for test variable"
        ];
        $r->assertStatus(201)->assertJson(['data' => [$expected]]);
        $id = $this->getIdFromTestResponse();
        $r = $this->findApiV6($path, $expected);
        $this->updateAttributeApiV6([Variable::FIELD_ONSET_DELAY=> 999], $id);
	    $r = $this->deleteApiV6($id);
	    self::setExpectedRequestException(\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException::class);
	    $r = $this->json(
		    'DELETE',
		    'variables/'. OverallMoodCommonVariable::ID
	    );
	    $r->assertStatus(405);
	    return $r;
    }
}
