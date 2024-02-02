<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Resources;

use Illuminate\Http\Request;

/** @mixin \App\Models\UserVariableRelationship */
class CorrelationResource extends BaseJsonResource
{
    use ResourceHasCharts;
    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        $arr = [];
        $arr = $this->addChartsOrUrl($arr);
        $arr = array_merge($arr, [
            'title' => $this->getTitleAttribute(),
            'id' => $this->id,
            'actions_count' => $this->actions_count,
            'global_variable_relationship_id' => $this->global_variable_relationship_id,
            'aggregated_at' => $this->aggregated_at,
            'analysis_ended_at' => $this->analysis_ended_at,
            'analysis_requested_at' => $this->analysis_requested_at,
            'analysis_started_at' => $this->analysis_started_at,
            'average_daily_high_cause' => $this->average_daily_high_cause,
            'average_daily_low_cause' => $this->average_daily_low_cause,
            'average_effect' => $this->average_effect,
            'average_effect_following_high_cause' => $this->average_effect_following_high_cause,
            'average_effect_following_low_cause' => $this->average_effect_following_low_cause,
            'average_forward_pearson_correlation_over_onset_delays' => $this->average_forward_pearson_correlation_over_onset_delays,
            'average_reverse_pearson_correlation_over_onset_delays' => $this->average_reverse_pearson_correlation_over_onset_delays,
            'boring' => $this->boring,
            'causality_vote' => $this->causality_vote,
            'cause_baseline_average_per_day' => $this->cause_baseline_average_per_day,
            'cause_baseline_average_per_duration_of_action' => $this->cause_baseline_average_per_duration_of_action,
            'cause_changes' => $this->cause_changes,
            'cause_filling_value' => $this->cause_filling_value,
            'cause_number_of_processed_daily_measurements' => $this->cause_number_of_processed_daily_measurements,
            'cause_number_of_raw_measurements' => $this->cause_number_of_raw_measurements,
            'cause_treatment_average_per_day' => $this->cause_treatment_average_per_day,
            'cause_treatment_average_per_duration_of_action' => $this->cause_treatment_average_per_duration_of_action,
            'cause_unit_id' => $this->cause_unit_id,
            'cause_user_variable_id' => $this->cause_user_variable_id,
            'cause_variable_category_id' => $this->cause_variable_category_id,
            'cause_variable_id' => $this->cause_variable_id,
            'client_id' => $this->client_id,
            'confidence_interval' => $this->confidence_interval,
            'confidence_level' => $this->confidence_level,
            'correlation' => $this->correlation,
            'correlation_causality_votes_count' => $this->correlation_causality_votes_count,
            'correlation_usefulness_votes_count' => $this->correlation_usefulness_votes_count,
            'correlations_over_delays' => $this->correlations_over_delays,
            'correlations_over_durations' => $this->correlations_over_durations,
            'created_at' => $this->created_at,
            'critical_t_value' => $this->critical_t_value,
            'data_source' => $this->data_source,
            'data_source_name' => $this->data_source_name,
            'deletion_reason' => $this->deletion_reason,
            'duration_of_action' => $this->duration_of_action,
            'earliest_measurement_start_at' => $this->earliest_measurement_start_at,
            'effect_baseline_average' => $this->effect_baseline_average,
            'effect_baseline_relative_standard_deviation' => $this->effect_baseline_relative_standard_deviation,
            'effect_baseline_standard_deviation' => $this->effect_baseline_standard_deviation,
            'effect_changes' => $this->effect_changes,
            'effect_filling_value' => $this->effect_filling_value,
            'effect_follow_up_average' => $this->effect_follow_up_average,
            'effect_follow_up_percent_change_from_baseline' => $this->effect_follow_up_percent_change_from_baseline,
            'effect_number_of_processed_daily_measurements' => $this->effect_number_of_processed_daily_measurements,
            'effect_number_of_raw_measurements' => $this->effect_number_of_raw_measurements,
            'effect_user_variable_id' => $this->effect_user_variable_id,
            'effect_variable_category_id' => $this->effect_variable_category_id,
            'effect_variable_id' => $this->effect_variable_id,
            'experiment_end_at' => $this->experiment_end_at,
            'experiment_start_at' => $this->experiment_start_at,
            'favoriters_count' => $this->favoriters_count,
            'forward_pearson_correlation_coefficient' => $this->forward_pearson_correlation_coefficient,
            'forward_spearman_correlation_coefficient' => $this->forward_spearman_correlation_coefficient,
            'grouped_cause_value_closest_to_value_predicting_high_outcome' => $this->grouped_cause_value_closest_to_value_predicting_high_outcome,
            'grouped_cause_value_closest_to_value_predicting_low_outcome' => $this->grouped_cause_value_closest_to_value_predicting_low_outcome,
            'interesting_variable_category_pair' => $this->interesting_variable_category_pair,
            //'internal_error_message' => $this->internal_error_message,
            'is_public' => $this->is_public,
            'latest_measurement_start_at' => $this->latest_measurement_start_at,
            'likers_count' => $this->likers_count,
            'media_count' => $this->media_count,
            'name' => $this->name,
            'newest_data_at' => $this->newest_data_at,
            'number_of_days' => $this->number_of_days,
            'number_of_down_votes' => $this->number_of_down_votes,
            'number_of_pairs' => $this->number_of_pairs,
            'number_of_up_votes' => $this->number_of_up_votes,
            'obvious' => $this->obvious,
            'onset_delay' => $this->onset_delay,
            'onset_delay_with_strongest_pearson_correlation' => $this->onset_delay_with_strongest_pearson_correlation,
            'optimal_pearson_product' => $this->optimal_pearson_product,
            'outcome_is_goal' => $this->outcome_is_goal,
            'p_value' => $this->p_value,
            'pearson_correlation_with_no_onset_delay' => $this->pearson_correlation_with_no_onset_delay,
            'plausibly_causal' => $this->plausibly_causal,
            'predictive_pearson_correlation_coefficient' => $this->predictive_pearson_correlation_coefficient,
            'predictor_is_controllable' => $this->predictor_is_controllable,
            'predicts_high_effect_change' => $this->predicts_high_effect_change,
            'predicts_low_effect_change' => $this->predicts_low_effect_change,
            'published_at' => $this->published_at,
            'qm_score' => $this->qm_score,
            'reason_for_analysis' => $this->reason_for_analysis,
            'record_size_in_kb' => $this->record_size_in_kb,
            'relationship' => $this->relationship,
            'reverse_pearson_correlation_coefficient' => $this->reverse_pearson_correlation_coefficient,
            'slug' => $this->getSlugWithNames(),
            'sort_order' => $this->sort_order,
            'statistical_significance' => $this->statistical_significance,
            //'status' => $this->status,
            'strength_level' => $this->strength_level,
            'strongest_pearson_correlation_coefficient' => $this->strongest_pearson_correlation_coefficient,
            'subtitle' => $this->getSubtitleAttribute(),
            't_value' => $this->t_value,
            'updated_at' => $this->updated_at,
            'usefulness_vote' => $this->usefulness_vote,
            //'user' => new UserResource($this->whenLoaded('user')),
            'user_error_message' => $this->user_error_message,
            'user_id' => $this->user_id,
            'user_variables_where_best_user_variable_relationship_count' => $this->user_variables_where_best_user_variable_relationship_count,
            'value_predicting_high_outcome' => $this->value_predicting_high_outcome,
            'value_predicting_low_outcome' => $this->value_predicting_low_outcome,
            'vote' => $this->vote,
            'votes_count' => $this->votes_count,
            'wp_post_id' => $this->wp_post_id,
            'z_score' => $this->z_score,
            //'favoriters' => UserCollection::collection($this->whenLoaded('favoriters')),
            //'likers' => UserCollection::collection($this->whenLoaded('likers')),
            //'timestamp' => $this->timestamp,
        ]);
        $arr = $this->addChartsOrUrl($arr);
        return $arr;
    }
}
