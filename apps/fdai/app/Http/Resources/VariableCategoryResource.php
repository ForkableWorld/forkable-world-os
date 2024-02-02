<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Resources;

use App\Types\QMStr;
use Illuminate\Http\Request;

/** @mixin \App\Models\VariableCategory */
class VariableCategoryResource extends BaseJsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'title' => $this->getTitleAttribute(),
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->getSlugWithNames(),
            'subtitle' => $this->getSubtitleAttribute(),
            'synonyms' => $this->synonyms,
            'name_singular' => $this->name_singular,
            'string_id' => QMStr::snakize($this->name),
            'amazon_product_category' => $this->amazon_product_category,
            'average_seconds_between_measurements' => $this->average_seconds_between_measurements,
            'boring' => $this->boring,
            'cause_only' => $this->cause_only,
            'combination_operation' => $this->combination_operation,
            'controllable' => $this->controllable,
            'default_unit_id' => $this->default_unit_id,
            'default_unit_name' => $this->getUnit()->name,
            'effect_only' => $this->effect_only,
            'filling_type' => $this->filling_type,
            'filling_value' => $this->filling_value,
            'font_awesome' => $this->font_awesome,
            'image_url' => $this->image_url,
            'ion_icon' => $this->ion_icon,
            'is_goal' => $this->is_goal,
            'is_public' => $this->is_public,
            'manual_tracking' => $this->manual_tracking,
            'maximum_allowed_value' => $this->maximum_allowed_value,
            'median_seconds_between_measurements' => $this->median_seconds_between_measurements,
            'minimum_allowed_seconds_between_measurements' => $this->minimum_allowed_seconds_between_measurements,
            'minimum_allowed_value' => $this->minimum_allowed_value,
            'more_info' => $this->more_info,
            'outcome' => $this->outcome,
            'predictor' => $this->predictor,
            'valence' => $this->valence,
            'variables_count' => $this->variables_count,
//'global_variable_relationships_count' => $this->global_variable_relationships_count,
//'global_variable_relationships_where_cause_variable_category' => GlobalVariableRelationshipResource::collection($this->whenLoaded('global_variable_relationships_where_cause_variable_category')),
//'global_variable_relationships_where_cause_variable_category_count' => $this->global_variable_relationships_where_cause_variable_category_count,
//'global_variable_relationships_where_effect_variable_category' => GlobalVariableRelationshipResource::collection($this->whenLoaded('global_variable_relationships_where_effect_variable_category')),
//'global_variable_relationships_where_effect_variable_category_count' => $this->global_variable_relationships_where_effect_variable_category_count,
//'correlations_count' => $this->correlations_count,
//'correlations_where_cause_variable_category' => CorrelationResource::collection($this->whenLoaded('correlations_where_cause_variable_category')),
//'correlations_where_cause_variable_category' => CorrelationResource::collection($this->whenLoaded('correlations_where_cause_variable_category')),
//'correlations_where_cause_variable_category_count' => $this->correlations_where_cause_variable_category_count,
//'correlations_where_effect_variable_category' => CorrelationResource::collection($this->whenLoaded('correlations_where_effect_variable_category')),
//'correlations_where_effect_variable_category' => CorrelationResource::collection($this->whenLoaded('correlations_where_effect_variable_category')),
//'correlations_where_effect_variable_category_count' => $this->correlations_where_effect_variable_category_count,
//'measurements' => MeasurementResource::collection($this->whenLoaded('measurements')),
//'measurements' => MeasurementResource::collection($this->whenLoaded('measurements')),
//'measurements_count' => $this->measurements_count,
//'number_of_measurements' => $this->number_of_measurements,
//'number_of_outcome_case_studies' => $this->number_of_outcome_case_studies,
//'number_of_outcome_population_studies' => $this->number_of_outcome_population_studies,
//'number_of_predictor_case_studies' => $this->number_of_predictor_case_studies,
//'number_of_predictor_population_studies' => $this->number_of_predictor_population_studies,
//'number_of_user_variables' => $this->number_of_user_variables,
//'number_of_variables' => $this->number_of_variables,
//'sort_order' => $this->sort_order,
//'tags_count' => $this->tags_count,
//'third_party_correlations_count' => $this->third_party_correlations_count,
//'third_party_correlations_where_cause_variable_category_count' => $this->third_party_correlations_where_cause_variable_category_count,
//'third_party_correlations_where_effect_variable_category_count' => $this->third_party_correlations_where_effect_variable_category_count,
//'user_variable_outcome_categories_count' => $this->user_variable_outcome_categories_count,
//'user_variable_predictor_categories_count' => $this->user_variable_predictor_categories_count,
//'user_variables' => UserVariableResource::collection($this->whenLoaded('user_variables')),
//'variable_outcome_categories_count' => $this->variable_outcome_categories_count,
//'variable_predictor_categories_count' => $this->variable_predictor_categories_count,
//'variables' => VariableResource::collection($this->whenLoaded('variables')),
//'client_id' => $this->client_id,
//'created_at' => $this->created_at,
//'duration_of_action' => $this->duration_of_action,
//'media_count' => $this->media_count,
//'onset_delay' => $this->onset_delay,
//'public' => $this->public,
//'raw' => $this->raw,
//'rule_for' => $this->rule_for,
//'rules_for' => $this->rules_for,
//'unit_id' => $this->unit_id,
//'updated_at' => $this->updated_at,
//'user_variables' => UserVariableResource::collection($this->whenLoaded('user_variables')),
//'user_variables_count' => $this->user_variables_count,
//'variables' => VariableResource::collection($this->whenLoaded('variables')),
//'wp_post_id' => $this->wp_post_id,
        ];
    }
}
