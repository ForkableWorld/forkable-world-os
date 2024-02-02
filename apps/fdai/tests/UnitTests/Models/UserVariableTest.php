<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\UnitTests\Models;
use App\Models\Variable;
use App\Storage\DB\TestDB;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use Tests\UnitTestCase;
/**
 * @package Tests\UnitTests\Models
 * @coversDefaultClass \App\Models\UserVariable
 */
class UserVariableTest extends UnitTestCase {
    public function testUserVariableCasts(){
        $v = Variable::find(OverallMoodCommonVariable::ID);
        if(!$v){
            TestDB::seed();
            $v = Variable::find(OverallMoodCommonVariable::ID);
        }
        $uv = $v->getOrCreateUserVariable(1);
        $dates = $uv->getDates();
        $casts = $uv->getCasts();
	    $expected = [
		    0 => 'analysis_ended_at',
		    1 => 'analysis_requested_at',
		    2 => 'analysis_settings_modified_at',
		    3 => 'analysis_started_at',
		    4 => 'earliest_non_tagged_measurement_start_at',
		    5 => 'earliest_source_measurement_start_at',
		    6 => 'earliest_tagged_measurement_start_at',
		    7 => 'last_correlated_at',
		    8 => 'latest_non_tagged_measurement_start_at',
		    9 => 'latest_source_measurement_start_at',
		    10 => 'latest_tagged_measurement_start_at',
		    11 => 'newest_data_at',
		    12 => 'created_at',
		    13 => 'updated_at',
		    19 => 'deleted_at',
	    ];
		sort($expected);
		sort($dates);
	    $this->assertArrayEquals($expected, $dates, "Wrong Date Casts");
        $this->assertArrayEquals(array (
	                                 'average_seconds_between_measurements' => 'int',
	                                 'best_cause_variable_id' => 'int',
	                                 'best_effect_variable_id' => 'int',
	                                 'best_user_variable_relationship_id' => 'int',
	                                 'cause_only' => 'bool',
	                                 'data_sources_count' => 'array',
	                                 'default_unit_id' => 'int',
	                                 'deleted_at' => 'datetime',
	                                 'duration_of_action' => 'int',
	                                 'earliest_filling_time' => 'int',
	                                 'filling_value' => 'float',
	                                 'id' => 'int',
	                                 'is_public' => 'bool',
	                                 'join_with' => 'int',
	                                 'kurtosis' => 'float',
	                                 'last_original_unit_id' => 'int',
	                                 'last_original_value' => 'float',
	                                 'last_processed_daily_value' => 'float',
	                                 'last_unit_id' => 'int',
	                                 'last_value' => 'float',
	                                 'latest_filling_time' => 'int',
	                                 'latitude' => 'float',
	                                 'longitude' => 'float',
	                                 'maximum_allowed_value' => 'float',
	                                 'maximum_recorded_value' => 'float',
	                                 'mean' => 'float',
	                                 'measurements_at_last_analysis' => 'int',
	                                 'median' => 'float',
	                                 'median_seconds_between_measurements' => 'int',
	                                 'minimum_allowed_seconds_between_measurements' => 'int',
	                                 'minimum_allowed_value' => 'float',
	                                 'minimum_recorded_value' => 'float',
	                                 'most_common_connector_id' => 'int',
	                                 'most_common_original_unit_id' => 'int',
	                                 'most_common_value' => 'float',
	                                 'number_of_changes' => 'int',
	                                 'number_of_correlations' => 'int',
	                                 'number_of_measurements' => 'int',
	                                 'number_of_measurements_with_tags_at_last_correlation' => 'int',
	                                 'number_of_processed_daily_measurements' => 'int',
	                                 'number_of_raw_measurements_with_tags_joins_children' => 'int',
	                                 'number_of_tracking_reminders' => 'int',
	                                 'number_of_unique_daily_values' => 'int',
	                                 'number_of_unique_values' => 'int',
	                                 'number_of_user_variable_relationships_as_cause' => 'int',
	                                 'number_of_user_variable_relationships_as_effect' => 'int',
	                                 'onset_delay' => 'int',
	                                 'outcome' => 'bool',
	                                 'outcome_of_interest' => 'bool',
	                                 'parent_id' => 'int',
	                                 'predictor_of_interest' => 'bool',
	                                 'second_to_last_value' => 'float',
	                                 'skewness' => 'float',
	                                 'standard_deviation' => 'float',
	                                 'third_to_last_value' => 'float',
	                                 'user_id' => 'int',
	                                 'user_maximum_allowed_daily_value' => 'float',
	                                 'user_minimum_allowed_daily_value' => 'float',
	                                 'user_minimum_allowed_non_zero_value' => 'float',
	                                 'variable_category_id' => 'int',
	                                 'variable_id' => 'int',
	                                 'variance' => 'float',
                                 ), $casts, "Wrong Casts");
    }
	public function testGetUrl(){
		$uv = $this->getOverallMoodUserVariable();
		$expected = \App\Utils\Env::getAppUrl().'/user-variables/'.$uv->getId();
		$this->assertEquals($expected, $uv->getUrl());
		$dmb = $uv->getQMUserVariable();
		$this->assertEquals($expected, $dmb->getUrl());
		$this->assertGetRedirect($expected, 'register');
		$this->actingAsUserOne();
		$response = $this->get($expected);
		$response->assertSee(OverallMoodCommonVariable::NAME);
	}

}
