<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class GlobalVariableRelationshipsTableSeeder extends AbstractSeeder
{
	public static function deleteGlobalVariableRelationships(){
		\DB::table('global_variable_relationships')->delete();
	}
	/**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('global_variable_relationships')->delete();

        \DB::table('global_variable_relationships')->insert(array (
            0 =>
            array (
                'id' => '1',
                'forward_pearson_correlation_coefficient' => '0.56904265960811',
                'onset_delay' => '1800',
                'duration_of_action' => '1814400',
                'number_of_pairs' => '119',
                'value_predicting_high_outcome' => '95.0',
                'value_predicting_low_outcome' => '27.966101694915',
                'optimal_pearson_product' => '0.64491503876398',
                'average_vote' => NULL,
                'number_of_users' => 2,
                'number_of_correlations' => 1,
                'statistical_significance' => '0.81343431377379',
                'cause_unit_id' => '7',
                'cause_changes' => '63',
                'effect_changes' => '3',
                'aggregate_qm_score' => '0.037677504126474',
                'created_at' => '2022-10-02 20:20:54',
                'updated_at' => '2022-10-02 20:20:59',
                'status' => 'UPDATED',
                'reverse_pearson_correlation_coefficient' => '0.0',
                'predictive_pearson_correlation_coefficient' => '0.23023717356136',
                'data_source_name' => 'user',
                'predicts_high_effect_change' => '25',
                'predicts_low_effect_change' => '-22',
                'p_value' => '0.001',
                't_value' => '5.9598301535337',
                'critical_t_value' => '1.646',
                'confidence_interval' => '0.53290643128457',
                'deleted_at' => NULL,
                'average_effect' => '3.0168067226891',
                'average_effect_following_high_cause' => '4.1020408163265',
                'average_effect_following_low_cause' => '2.2571428571429',
                'average_daily_low_cause' => '16.836734693878',
                'average_daily_high_cause' => '125.94752186589',
                'population_trait_pearson_correlation_coefficient' => NULL,
                'grouped_cause_value_closest_to_value_predicting_low_outcome' => '0.0',
                'grouped_cause_value_closest_to_value_predicting_high_outcome' => '1800.0',
                'client_id' => NULL,
                'published_at' => NULL,
                'wp_post_id' => NULL,
                'cause_variable_category_id' => '13',
                'effect_variable_category_id' => '1',
                'interesting_variable_category_pair' => '1',
                'newest_data_at' => '2022-10-02 20:20:57',
                'analysis_requested_at' => NULL,
                'reason_for_analysis' => 'STALE: analysis_ended_at before newest_data_at',
                'analysis_started_at' => '2022-10-02 20:20:59',
                'analysis_ended_at' => '2022-10-02 20:20:59',
                'user_error_message' => NULL,
                'internal_error_message' => NULL,
                'cause_variable_id' => '1276',
                'effect_variable_id' => '1398',
                'cause_baseline_average_per_day' => '0.57619047619048',
                'cause_baseline_average_per_duration_of_action' => '12.1',
                'cause_treatment_average_per_day' => '5.7142857142857',
                'cause_treatment_average_per_duration_of_action' => '120.0',
                'effect_baseline_average' => '2.13',
                'effect_baseline_relative_standard_deviation' => '85.1',
                'effect_baseline_standard_deviation' => '1.8126539343499',
                'effect_follow_up_average' => '4.0545454545455',
                'effect_follow_up_percent_change_from_baseline' => '90.4',
                'z_score' => '1.06',
            'charts' => '{"populationTraitScatterPlot":{"chartId":"trait-relationship-between-bupropion-sr-intake-and-overall-mood","chartTitle":"Trait User Variable Relationship Between Bupropion Sr Intake and Overall Mood","explanation":"People with higher Bupropion Sr Intake usually have lower Overall Mood","highchartConfig":{"chart":{"renderTo":"trait-relationship-between-bupropion-sr-intake-and-overall-mood-chart-container","type":"scatter","zoomType":"xy"},"title":{"text":"Trait User Variable Relationship Between Bupropion Sr Intake and Overall Mood","enabled":true},"subtitle":{"text":"People with higher Bupropion Sr Intake usually have lower Overall Mood"},"xAxis":{"title":{"text":"Average Bupropion Sr Intake (mg) for Participant"}},"yAxis":{"title":{"text":"Average Overall Mood (\\/5) for Participant","enabled":true}},"legend":{"enabled":true},"series":[{"name":"Overall Mood by Bupropion Sr","data":[[75,3]],"marker":{"enabled":true,"radius":5},"color":"#000000","visible":true}],"tooltip":{"formatter":{"_expression":"function() {\\n            var series = this.series || this.points[0].series || this.points[0].chart.series;\\n            var tooltips = series.options.tooltips;\\n            if(tooltips){\\n                var x = this.x || this.point.x\\n                var tooltip = tooltips[x] || null;\\n                if(tooltip){\\n                    \\/\\/console.warn(this.point)\\n                    \\/\\/debugger\\n                    return tooltip\\n                }\\n            }\\n            \\n            return \'People with an average of \' + this.x +\\n                \'mg Bupropion Sr<br\\/> typically exhibit an average of <br\\/>\' +\\n                this.y + \'\\/5 Overall Mood\';\\n        \\n        }"}},"colors":["#000000","#3467d6","#dd4b39","#0f9d58","#f09402","#d34836","#886aea"],"credits":{"enabled":false},"lang":{"loading":""},"loading":{"hideDuration":10,"showDuration":10},"plotOptions":{"scatter":{"tooltip":{"formatter":{"_expression":"function() {\\n            var series = this.series || this.points[0].series || this.points[0].chart.series;\\n            var tooltips = series.options.tooltips;\\n            if(tooltips){\\n                var x = this.x || this.point.x\\n                var tooltip = tooltips[x] || null;\\n                if(tooltip){\\n                    \\/\\/console.warn(this.point)\\n                    \\/\\/debugger\\n                    return tooltip\\n                }\\n            }\\n            \\n            return \'People with an average of \' + this.x +\\n                \'mg Bupropion Sr<br\\/> typically exhibit an average of <br\\/>\' +\\n                this.y + \'\\/5 Overall Mood\';\\n        \\n        }"}}}},"useHighStocks":false,"exporting":{"enabled":true},"id":"trait-relationship-between-bupropion-sr-intake-and-overall-mood","themeName":"white","divHeight":null,"type":"Population Trait User Variable Relationship Scatter Plot"},"id":"trait-relationship-between-bupropion-sr-intake-and-overall-mood","imageGeneratedAt":null,"imageUrl":null,"jpgUrl":null,"pngUrl":null,"subtitle":null,"svgUrl":null,"title":"Trait User Variable Relationship Between Bupropion Sr Intake and Overall Mood","validImageOnS3":null,"variableName":null},"id":null}',
                'number_of_variables_where_best_global_variable_relationship' => '0',
                'deletion_reason' => NULL,
                'record_size_in_kb' => NULL,
                'is_public' => '1',
                'slug' => NULL,
                'boring' => NULL,
                'outcome_is_a_goal' => NULL,
                'predictor_is_controllable' => NULL,
                'plausibly_causal' => NULL,
                'obvious' => NULL,
                'number_of_up_votes' => '0',
                'number_of_down_votes' => '0',
                'strength_level' => 'MODERATE',
                'confidence_level' => 'MEDIUM',
                'relationship' => 'POSITIVE',
            ),
        ));


    }
}
