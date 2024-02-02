<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Resources;

use Illuminate\Http\Request;

/** @mixin \App\Models\Unit */
class UnitResource extends BaseJsonResource
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
            'synonyms' => $this->synonyms,
            'slug' => $this->getSlugWithNames(),
            'abbreviated_name' => $this->abbreviated_name,
            'advanced' => $this->advanced,
            'unit_category_id' => $this->unit_category_id,
            'unit_category_name' => $this->getUnitCategory()->name,
            'conversion_steps' => $this->conversion_steps,
            'filling_type' => $this->filling_type,
            'filling_value' => $this->filling_value,
            'manual_tracking' => $this->manual_tracking,
            'maximum_daily_value' => $this->maximum_daily_value,
            'maximum_value' => $this->maximum_value,
            'minimum_value' => $this->minimum_value,
            'number_of_variables_where_default_unit' => $this->number_of_variables_where_default_unit,
            'scale' => $this->scale,
            'sort_order' => $this->sort_order,
//'global_variable_relationships_count' => $this->global_variable_relationships_count,
//'global_variable_relationships_where_cause_unit' => GlobalVariableRelationshipResource::collection($this->whenLoaded('global_variable_relationships_where_cause_unit')),
//'global_variable_relationships_where_cause_unit' => GlobalVariableRelationshipResource::collection($this->whenLoaded('global_variable_relationships_where_cause_unit')),
//'global_variable_relationships_where_cause_unit_count' => $this->global_variable_relationships_where_cause_unit_count,
//'aggregateCorrelations' => GlobalVariableRelationshipResource::collection($this->whenLoaded('aggregateCorrelations')),
//'aggregateCorrelations' => GlobalVariableRelationshipResource::collection($this->whenLoaded('aggregateCorrelations')),
//'common_tags_count' => $this->common_tags_count,
//'common_tags_where_tag_variable_unit_count' => $this->common_tags_where_tag_variable_unit_count,
//'common_tags_where_tagged_variable_unit_count' => $this->common_tags_where_tagged_variable_unit_count,
//'user_variable_relationships' => CorrelationResource::collection($this->whenLoaded('user_variable_relationships')),
//'user_variable_relationships' => CorrelationResource::collection($this->whenLoaded('user_variable_relationships')),
//'correlations_count' => $this->correlations_count,
//'correlations_where_cause_unit' => CorrelationResource::collection($this->whenLoaded('correlations_where_cause_unit')),
//'correlations_where_cause_unit' => CorrelationResource::collection($this->whenLoaded('correlations_where_cause_unit')),
//'correlations_where_cause_unit_count' => $this->correlations_where_cause_unit_count,
//'measurements' => MeasurementResource::collection($this->whenLoaded('measurements')),
//'measurements' => MeasurementResource::collection($this->whenLoaded('measurements')),
//'measurements_count' => $this->measurements_count,
//'measurements_where_original_unit' => MeasurementResource::collection($this->whenLoaded('measurements_where_original_unit')),
//'measurements_where_original_unit' => MeasurementResource::collection($this->whenLoaded('measurements_where_original_unit')),
//'measurements_where_original_unit_count' => $this->measurements_where_original_unit_count,
//'number_of_common_tags_where_tag_variable_unit' => $this->number_of_common_tags_where_tag_variable_unit,
//'number_of_common_tags_where_tagged_variable_unit' => $this->number_of_common_tags_where_tagged_variable_unit,
//'number_of_measurements' => $this->number_of_measurements,
//'number_of_outcome_case_studies' => $this->number_of_outcome_case_studies,
//'number_of_outcome_population_studies' => $this->number_of_outcome_population_studies,
//'number_of_user_variables_where_default_unit' => $this->number_of_user_variables_where_default_unit,
//'number_of_variable_categories_where_default_unit' => $this->number_of_variable_categories_where_default_unit,
//'user_variables_count' => $this->user_variables_count,
//'user_variables_where_default_unit' => UserVariableResource::collection($this->whenLoaded('user_variables_where_default_unit')),
//'user_variables_where_default_unit' => UserVariableResource::collection($this->whenLoaded('user_variables_where_default_unit')),
//'user_variables_where_default_unit_count' => $this->user_variables_where_default_unit_count,
//'user_variables_where_last_unit' => UserVariableResource::collection($this->whenLoaded('user_variables_where_last_unit')),
//'user_variables_where_last_unit' => UserVariableResource::collection($this->whenLoaded('user_variables_where_last_unit')),
//'user_variables_where_last_unit_count' => $this->user_variables_where_last_unit_count,
//'userVariables' => UserVariableResource::collection($this->whenLoaded('userVariables')),
//'userVariables' => UserVariableResource::collection($this->whenLoaded('userVariables')),
//'variable_categories_count' => $this->variable_categories_count,
//'variable_categories_where_default_unit' => VariableCategoryResource::collection($this->whenLoaded('variable_categories_where_default_unit')),
//'variable_categories_where_default_unit' => VariableCategoryResource::collection($this->whenLoaded('variable_categories_where_default_unit')),
//'variable_categories_where_default_unit_count' => $this->variable_categories_where_default_unit_count,
//'variableCategories' => VariableCategoryResource::collection($this->whenLoaded('variableCategories')),
//'variableCategories' => VariableCategoryResource::collection($this->whenLoaded('variableCategories')),
//'variables' => VariableResource::collection($this->whenLoaded('variables')),
//'variables' => VariableResource::collection($this->whenLoaded('variables')),
//'variables_count' => $this->variables_count,
//'variables_where_default_unit' => VariableResource::collection($this->whenLoaded('variables_where_default_unit')),
//'variables_where_default_unit' => VariableResource::collection($this->whenLoaded('variables_where_default_unit')),
//'variables_where_default_unit_count' => $this->variables_where_default_unit_count,
//'created_at' => $this->created_at,
//'raw' => $this->raw,
//'report_title' => $this->report_title,
//'rule_for' => $this->rule_for,
//'rules_for' => $this->rules_for,
//'subtitle' => $this->getSubtitleAttribute(),
//'tags_count' => $this->tags_count,
//'updated_at' => $this->updated_at,
        ];
    }
}
