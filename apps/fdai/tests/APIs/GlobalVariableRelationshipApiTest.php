<?php /** @noinspection PhpUnhandledExceptionInspection */

namespace Tests\APIs;
use App\Models\BaseModel;
use App\Models\User;
use App\Models\Variable;
use Database\Seeders\WpUsersTableSeeder;
use Illuminate\Foundation\Testing\Concerns\InteractsWithDatabase;
use Tests\ApiTestTrait;
use Tests\UnitTestCase;
use App\Models\GlobalVariableRelationship;
class GlobalVariableRelationshipApiTest extends UnitTestCase
{
    use ApiTestTrait, InteractsWithDatabase;
	/**
	 * @return void
	 */
	public function seedUserTable(): void{
		User::deleteAll();
		(new WpUsersTableSeeder())->run();
		$all = User::all();
		foreach($all as $user){
			$user->user_pass = 'testing123';
			$user->save();
		}
	}
	/**
	 * @return void
	 */
	public function checkUserTable(): void{
		$users = User::pluck(User::FIELD_USER_LOGIN)->all();
		$this->assertArrayEquals([
			0 => 'quantimodo',
			1 => 'quint',
			2 => 'asdfds',
			3 => 'no-user_variable_relationships-user',
			4 => 'system',
			5 => 'dr-quantimo-do',
			6 => 'testuser1499206388501',
			7 => 'mike',
			8 => 'demo',
			9 => 'population',
			10 => 'testuser'
		], $users);
	}
	public function setUp(): void{
        //$this->assertEquals("quantimodo_test", DB::connection()->getDatabaseName());
        parent::setUp();
    }
	/**
	 * @return void
	 * @throws \Throwable
	 * @covers \App\Http\Controllers\API\GlobalVariableRelationshipAPIController::index
	 */
    public function test_read_global_variable_relationships(): void{
	    $this->checkUserTable();
	    $this->checkMeasurements();
	    $ac = GlobalVariableRelationship::first();
        if(!$ac) {
            $this->fail("No global variable relationships found");
        }
        $this->assertNotNull($ac);
        $id = $ac->id;
		$this->assertEquals(2, $ac->number_of_users);
        $r = $this->jsonAsUser18535(
            'GET',
            '/api/v6/global_variable_relationships/'.$id
        );
        $r->assertStatus(200);
        $data = $this->getJsonResponseData();
        $this->compareObjectFixture('global_variable_relationships', $data);
        $ac = GlobalVariableRelationship::fakeSaveFromPropertyModels();
        $r = $this->jsonAsUser18535(
            'GET',
            '/api/v6/global_variable_relationships'
        );
        $r->assertStatus(200);
        $data = $this->getJsonResponseData();
        //var_export($data);
        $this->assertEquals(array (
	                            0 =>
		                            array (
			                            'charts' =>
				                            array (
					                            'populationTraitScatterPlot' =>
						                            array (
							                            'chartId' => 'trait-relationship-between-bupropion-sr-intake-and-overall-mood',
							                            'chartTitle' => 'Trait User Variable Relationship Between Bupropion Sr Intake and Overall Mood',
							                            'explanation' => 'People with higher Bupropion Sr Intake usually have lower Overall Mood',
							                            'highchartConfig' =>
								                            array (
									                            'chart' =>
										                            array (
											                            'renderTo' => 'trait-relationship-between-bupropion-sr-intake-and-overall-mood-chart-container',
											                            'type' => 'scatter',
											                            'zoomType' => 'xy',
										                            ),
									                            'title' =>
										                            array (
											                            'text' => 'Trait User Variable Relationship Between Bupropion Sr Intake and Overall Mood',
											                            'enabled' => true,
										                            ),
									                            'subtitle' =>
										                            array (
											                            'text' => 'People with higher Bupropion Sr Intake usually have lower Overall Mood',
										                            ),
									                            'xAxis' =>
										                            array (
											                            'title' =>
												                            array (
													                            'text' => 'Average Bupropion Sr Intake (mg) for Participant',
												                            ),
										                            ),
									                            'yAxis' =>
										                            array (
											                            'title' =>
												                            array (
													                            'text' => 'Average Overall Mood (/5) for Participant',
													                            'enabled' => true,
												                            ),
										                            ),
									                            'legend' =>
										                            array (
											                            'enabled' => true,
										                            ),
									                            'series' =>
										                            array (
											                            0 =>
												                            array (
													                            'name' => 'Overall Mood by Bupropion Sr',
													                            'data' =>
														                            array (
															                            0 =>
																                            array (
																	                            0 => 75,
																	                            1 => 3,
																                            ),
														                            ),
													                            'marker' =>
														                            array (
															                            'enabled' => true,
															                            'radius' => 5,
														                            ),
													                            'color' => '#000000',
													                            'visible' => true,
												                            ),
										                            ),
									                            'tooltip' =>
										                            array (
											                            'formatter' =>
												                            array (
													                            '_expression' => 'function() {
            var series = this.series || this.points[0].series || this.points[0].chart.series;
            var tooltips = series.options.tooltips;
            if(tooltips){
                var x = this.x || this.point.x
                var tooltip = tooltips[x] || null;
                if(tooltip){
                    //console.warn(this.point)
                    //debugger
                    return tooltip
                }
            }
            
            return \'People with an average of \' + this.x +
                \'mg Bupropion Sr<br/> typically exhibit an average of <br/>\' +
                this.y + \'/5 Overall Mood\';
        
        }',
												                            ),
										                            ),
									                            'colors' =>
										                            array (
											                            0 => '#000000',
											                            1 => '#3467d6',
											                            2 => '#dd4b39',
											                            3 => '#0f9d58',
											                            4 => '#f09402',
											                            5 => '#d34836',
											                            6 => '#886aea',
										                            ),
									                            'credits' =>
										                            array (
											                            'enabled' => false,
										                            ),
									                            'lang' =>
										                            array (
											                            'loading' => '',
										                            ),
									                            'loading' =>
										                            array (
											                            'hideDuration' => 10,
											                            'showDuration' => 10,
										                            ),
									                            'plotOptions' =>
										                            array (
											                            'scatter' =>
												                            array (
													                            'tooltip' =>
														                            array (
															                            'formatter' =>
																                            array (
																	                            '_expression' => 'function() {
            var series = this.series || this.points[0].series || this.points[0].chart.series;
            var tooltips = series.options.tooltips;
            if(tooltips){
                var x = this.x || this.point.x
                var tooltip = tooltips[x] || null;
                if(tooltip){
                    //console.warn(this.point)
                    //debugger
                    return tooltip
                }
            }
            
            return \'People with an average of \' + this.x +
                \'mg Bupropion Sr<br/> typically exhibit an average of <br/>\' +
                this.y + \'/5 Overall Mood\';
        
        }',
																                            ),
														                            ),
												                            ),
										                            ),
									                            'useHighStocks' => false,
									                            'exporting' =>
										                            array (
											                            'enabled' => true,
										                            ),
									                            'id' => 'trait-relationship-between-bupropion-sr-intake-and-overall-mood',
									                            'themeName' => 'white',
									                            'divHeight' => NULL,
									                            'type' => 'Population Trait User Variable Relationship Scatter Plot',
								                            ),
							                            'id' => 'trait-relationship-between-bupropion-sr-intake-and-overall-mood',
							                            'imageGeneratedAt' => NULL,
							                            'imageUrl' => NULL,
							                            'jpgUrl' => NULL,
							                            'pngUrl' => NULL,
							                            'subtitle' => NULL,
							                            'svgUrl' => NULL,
							                            'title' => 'Trait User Variable Relationship Between Bupropion Sr Intake and Overall Mood',
							                            'validImageOnS3' => NULL,
							                            'variableName' => NULL,
						                            ),
					                            'id' => NULL,
				                            ),
			                            'id' => 1,
			                            'actions_count' => NULL,
			                            'aggregate_qm_score' => 0.037677504126474,
			                            'analysis_ended_at' => '2022-10-02T20:20:59.000000Z',
			                            'analysis_requested_at' => NULL,
			                            'analysis_started_at' => '2022-12-19T14:47:44.000000Z',
			                            'average_daily_high_cause' => 125.94752186589,
			                            'average_daily_low_cause' => 16.836734693878,
			                            'average_effect' => 3.0168067226891,
			                            'average_effect_following_high_cause' => 4.1020408163265,
			                            'average_effect_following_low_cause' => 2.2571428571429,
			                            'average_vote' => NULL,
			                            'boring' => NULL,
			                            'cause_baseline_average_per_day' => 0.57619047619048,
			                            'cause_baseline_average_per_duration_of_action' => 12.1,
			                            'cause_changes' => 63,
			                            'cause_treatment_average_per_day' => 5.7142857142857,
			                            'cause_treatment_average_per_duration_of_action' => 120,
			                            'cause_unit_id' => 7,
			                            'cause_variable_category_id' => 13,
			                            'cause_variable_id' => 1276,
			                            'client_id' => NULL,
			                            'confidence_interval' => 0.53290643128457,
			                            'confidence_level' => 'HIGH',
			                            'correlation_causality_votes_count' => NULL,
			                            'correlation_usefulness_votes_count' => NULL,
			                            'correlations_count' => NULL,
			                            'created_at' => '2022-10-02T20:20:54.000000Z',
			                            'critical_t_value' => 1.646,
			                            'data_source' => NULL,
			                            'data_source_name' => 'user',
			                            'deletion_reason' => NULL,
			                            'duration_of_action' => 1814400,
			                            'effect_baseline_average' => 2.13,
			                            'effect_baseline_relative_standard_deviation' => 85.1,
			                            'effect_baseline_standard_deviation' => 1.8126539343499,
			                            'effect_changes' => 3,
			                            'effect_follow_up_average' => 4.0545454545455,
			                            'effect_follow_up_percent_change_from_baseline' => 90.4,
			                            'effect_variable_category_id' => 1,
			                            'effect_variable_id' => 1398,
			                            'favoriters_count' => NULL,
			                            'favorites_count' => NULL,
			                            'forward_pearson_correlation_coefficient' => 0.56904265960811,
			                            'grouped_cause_value_closest_to_value_predicting_high_outcome' => 1800,
			                            'grouped_cause_value_closest_to_value_predicting_low_outcome' => 0,
			                            'interesting_variable_category_pair' => true,
			                            'is_public' => true,
			                            'likers_count' => NULL,
			                            'likes_count' => NULL,
			                            'media_count' => NULL,
			                            'name' => 'Relationship Between Bupropion Sr and Overall Mood',
			                            'newest_data_at' => '2022-12-19T14:47:38.000000Z',
			                            'number_of_correlations' => 2,
			                            'number_of_down_votes' => 0,
			                            'number_of_pairs' => 119,
			                            'number_of_up_votes' => 0,
			                            'number_of_users' => 2,
			                            'number_of_variables_where_best_global_variable_relationship' => 0,
			                            'obvious' => NULL,
			                            'onset_delay' => 1800,
			                            'optimal_pearson_product' => 0.64491503876398,
			                            'outcome_is_a_goal' => NULL,
			                            'p_value' => 0.001,
			                            'plausibly_causal' => NULL,
			                            'population_trait_pearson_correlation_coefficient' => NULL,
			                            'predictive_pearson_correlation_coefficient' => 0.23023717356136,
			                            'predictor_is_controllable' => NULL,
			                            'predicts_high_effect_change' => 25,
			                            'predicts_low_effect_change' => -22,
			                            'published_at' => NULL,
			                            'reason_for_analysis' => 'testUpdateTestDB',
			                            'record_size_in_kb' => NULL,
			                            'relationship' => 'POSITIVE',
			                            'reverse_pearson_correlation_coefficient' => 0,
			                            'slug' => 'cause-1276-effect-1398-population-study',
			                            'statistical_significance' => 0.81343431377379,
			                            'strength_level' => 'MODERATE',
			                            'subtitle' => 'Overall Mood was ↑90% Higher following above average Bupropion Sr.  Based on 119 days of data from 12 participants.',
			                            't_value' => 5.9598301535337,
			                            'title' => 'Higher Bupropion Sr Intake Predicts Moderately Higher Overall Mood for Population',
			                            'updated_at' => '2022-12-19T14:47:44.000000Z',
			                            'user_error_message' => NULL,
			                            'value_predicting_high_outcome' => 95,
			                            'value_predicting_low_outcome' => 27.966101694915,
			                            'variables_where_best_global_variable_relationship' =>
				                            array (
				                            ),
			                            'variables_where_best_global_variable_relationship_count' => NULL,
			                            'vote' => NULL,
			                            'votes_count' => NULL,
			                            'wp_post_id' => NULL,
			                            'z_score' => 1.06,
		                            ),
                            ), $data);
        $this->expectUnauthorizedException();
        $r = $this->jsonAsUser18535(
            'PUT',
            '/api/v6/global_variable_relationships/'.$ac->id,
            [GlobalVariableRelationship::FIELD_PREDICTS_LOW_EFFECT_CHANGE => 1]
        );
        $r->assertStatus(401);
        $this->expectUnauthorizedException();
        $r = $this->jsonAsUser18535(
            'DELETE',
             '/api/v6/global_variable_relationships/'.$ac->id
         );
        $r->assertStatus(401);
        $r = $this->jsonAsUser18535(
            'GET',
            '/api/v6/global_variable_relationships/'.$ac->id
        );
        $r->assertStatus(200);
        $ac = $this->getModel();
        $r = $this->get('/studies/'.$ac->getStudyId())
            ->assertStatus(200);
        $this->compareHtmlPage(__FUNCTION__, $r->getContent(), false);
    }
    /**
     * @return GlobalVariableRelationship
     */
    public function getModel(): BaseModel {
        return parent::getModel();
    }
	/**
	 * @return void
	 */
	public function checkMeasurements(): void{
		$mood = Variable::find(1398);
		//$mood->analyze();
		$measurements = $mood->measurements()->get();
		$this->assertEquals(240, $measurements->count());
		$this->assertEquals("2019-12-31 00:00:00", db_date($mood->latest_non_tagged_measurement_start_at));
		$this->assertEquals('2019-12-31 00:00:00', db_date($mood->latest_tagged_measurement_start_at));
	}
}
