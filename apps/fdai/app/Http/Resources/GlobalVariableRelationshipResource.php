<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Resources;

use Illuminate\Http\Request;

/** @mixin \App\Models\GlobalVariableRelationship */
class GlobalVariableRelationshipResource extends BaseJsonResource
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
            'id' => $this->id,
            //'correlation' => $this->correlation,
            'actions_count' => $this->actions_count,
            'aggregate_qm_score' => $this->aggregate_qm_score,
            'analysis_ended_at' => $this->analysis_ended_at,
            'analysis_requested_at' => $this->analysis_requested_at,
            'analysis_started_at' => $this->analysis_started_at,
            'average_daily_high_cause' => $this->average_daily_high_cause,
            'average_daily_low_cause' => $this->average_daily_low_cause,
            'average_effect' => $this->average_effect,
            'average_effect_following_high_cause' => $this->average_effect_following_high_cause,
            'average_effect_following_low_cause' => $this->average_effect_following_low_cause,
            'average_vote' => $this->average_vote,
            'boring' => $this->boring,
            'cause_baseline_average_per_day' => $this->cause_baseline_average_per_day,
            'cause_baseline_average_per_duration_of_action' => $this->cause_baseline_average_per_duration_of_action,
            'cause_changes' => $this->cause_changes,
            'cause_treatment_average_per_day' => $this->cause_treatment_average_per_day,
            'cause_treatment_average_per_duration_of_action' => $this->cause_treatment_average_per_duration_of_action,
            'cause_unit_id' => $this->cause_unit_id,
            'cause_variable' => new VariableResource($this->whenLoaded('cause_variable')),
            'cause_variable_category_id' => $this->cause_variable_category_id,
            'cause_variable_id' => $this->cause_variable_id,
            'charts' => $this->charts,
            'client_id' => $this->client_id,
            'confidence_interval' => $this->confidence_interval,
            'confidence_level' => $this->confidence_level,
            'correlation_causality_votes_count' => $this->correlation_causality_votes_count,
            'correlation_usefulness_votes_count' => $this->correlation_usefulness_votes_count,
            //'user_variable_relationships' => CorrelationResource::collection($this->whenLoaded('user_variable_relationships')),
            'correlations_count' => $this->correlations_count,
            'created_at' => $this->created_at,
            'critical_t_value' => $this->critical_t_value,
            'data_source' => $this->data_source,
            'data_source_name' => $this->data_source_name,
            'deletion_reason' => $this->deletion_reason,
            'duration_of_action' => $this->duration_of_action,
            'effect_baseline_average' => $this->effect_baseline_average,
            'effect_baseline_relative_standard_deviation' => $this->effect_baseline_relative_standard_deviation,
            'effect_baseline_standard_deviation' => $this->effect_baseline_standard_deviation,
            'effect_changes' => $this->effect_changes,
            'effect_follow_up_average' => $this->effect_follow_up_average,
            'effect_follow_up_percent_change_from_baseline' => $this->effect_follow_up_percent_change_from_baseline,
            'effect_variable' => new VariableResource($this->whenLoaded('effect_variable')),
            'effect_variable_category_id' => $this->effect_variable_category_id,
            'effect_variable_id' => $this->effect_variable_id,
            //'favoriters' => UserCollection::collection($this->whenLoaded('favoriters')),
            'favoriters_count' => $this->favoriters_count,
            'favorites_count' => $this->favorites_count,
            'forward_pearson_correlation_coefficient' => $this->forward_pearson_correlation_coefficient,
            'grouped_cause_value_closest_to_value_predicting_high_outcome' => $this->grouped_cause_value_closest_to_value_predicting_high_outcome,
            'grouped_cause_value_closest_to_value_predicting_low_outcome' => $this->grouped_cause_value_closest_to_value_predicting_low_outcome,
            'interesting_variable_category_pair' => $this->interesting_variable_category_pair,
            //'internal_error_message' => $this->internal_error_message,
            'is_public' => $this->is_public,
            //'likers' => UserCollection::collection($this->whenLoaded('likers')),
            'likers_count' => $this->likers_count,
            'likes_count' => $this->likes_count,
            'media_count' => $this->media_count,
            'name' => $this->name,
            'newest_data_at' => $this->newest_data_at,
            'number_of_correlations' => $this->number_of_correlations,
            'number_of_down_votes' => $this->number_of_down_votes,
            'number_of_pairs' => $this->number_of_pairs,
            'number_of_up_votes' => $this->number_of_up_votes,
            'number_of_users' => $this->number_of_users,
            'number_of_variables_where_best_global_variable_relationship' => $this->number_of_variables_where_best_global_variable_relationship,
            'obvious' => $this->obvious,
            'onset_delay' => $this->onset_delay,
            'optimal_pearson_product' => $this->optimal_pearson_product,
            'outcome_is_a_goal' => $this->outcome_is_a_goal,
            'p_value' => $this->p_value,
            'plausibly_causal' => $this->plausibly_causal,
            'population_trait_pearson_correlation_coefficient' => $this->population_trait_pearson_correlation_coefficient,
            'predictive_pearson_correlation_coefficient' => $this->predictive_pearson_correlation_coefficient,
            'predictor_is_controllable' => $this->predictor_is_controllable,
            'predicts_high_effect_change' => $this->predicts_high_effect_change,
            'predicts_low_effect_change' => $this->predicts_low_effect_change,
            'published_at' => $this->published_at,
            'reason_for_analysis' => $this->reason_for_analysis,
            'record_size_in_kb' => $this->record_size_in_kb,
            'relationship' => $this->relationship,
            'reverse_pearson_correlation_coefficient' => $this->reverse_pearson_correlation_coefficient,
            'slug' => $this->getSlugWithNames(),
            'statistical_significance' => $this->statistical_significance,
            //'status' => $this->status,
            'strength_level' => $this->strength_level,
            'subtitle' => $this->getSubtitleAttribute(),
            't_value' => $this->t_value,
            'title' => $this->getTitleAttribute(),
            'updated_at' => $this->updated_at,
            'user_error_message' => $this->user_error_message,
            'value_predicting_high_outcome' => $this->value_predicting_high_outcome,
            'value_predicting_low_outcome' => $this->value_predicting_low_outcome,
            'variables_where_best_global_variable_relationship' => VariableResource::collection($this->whenLoaded('variables_where_best_global_variable_relationship')),
            //'variables_where_best_global_variable_relationship' => VariableResource::collection($this->whenLoaded
            //('variables_where_best_global_variable_relationship')),
            'variables_where_best_global_variable_relationship_count' => $this->variables_where_best_global_variable_relationship_count,
            'vote' => $this->vote,
            'votes_count' => $this->votes_count,
            'wp_post_id' => $this->wp_post_id,
            'z_score' => $this->z_score,
        ]);
        $arr = $this->addChartsOrUrl($arr);
        return $arr;
    }
}
