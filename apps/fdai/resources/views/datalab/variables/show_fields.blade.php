<?php /** @var App\Models\Variable $variable */ ?>
<!-- Name Field -->
<div class="form-group">
    {!! Form::label('name', 'Name:') !!}
    <p>{{ $variable->name }}</p>
</div>

<!-- Number Of User Variables Field -->
<div class="form-group">
    {!! Form::label('number_of_user_variables', 'Number Of User Variables:') !!}
    <p>{{ $variable->number_of_user_variables }}</p>
</div>

<!-- Variable Category Id Field -->
<div class="form-group">
    {!! Form::label('variable_category_id', 'Variable Category Id:') !!}
    <p>{{ $variable->variable_category_id }}</p>
</div>

<!-- Default Unit Id Field -->
<div class="form-group">
    {!! Form::label('default_unit_id', 'Default Unit Id:') !!}
    <p>{{ $variable->default_unit_id }}</p>
</div>

<!-- Default Value Field -->
<div class="form-group">
    {!! Form::label('default_value', 'Default Value:') !!}
    <p>{{ $variable->default_value }}</p>
</div>

<!-- Public Field -->
<div class="form-group">
    {!! Form::label('is_public', 'Public:') !!}
    <p>{{ $variable->is_public }}</p>
</div>

<!-- Cause Only Field -->
<div class="form-group">
    {!! Form::label('cause_only', 'Cause Only:') !!}
    <p>{{ $variable->cause_only }}</p>
</div>

<!-- Client Id Field -->
<div class="form-group">
    {!! Form::label('client_id', 'Client Id:') !!}
    <p>{{ $variable->client_id }}</p>
</div>

<!-- Combination Operation Field -->
<div class="form-group">
    {!! Form::label('combination_operation', 'Combination Operation:') !!}
    <p>{{ $variable->combination_operation }}</p>
</div>

<!-- Common Alias Field -->
<div class="form-group">
    {!! Form::label('common_alias', 'Common Alias:') !!}
    <p>{{ $variable->common_alias }}</p>
</div>

<!-- Description Field -->
<div class="form-group">
    {!! Form::label('description', 'Description:') !!}
    <p>{{ $variable->description }}</p>
</div>

<!-- Duration Of Action Field -->
<div class="form-group">
    {!! Form::label('duration_of_action', 'Duration Of Action:') !!}
    <p>{{ $variable->duration_of_action }}</p>
</div>

<!-- Filling Value Field -->
<div class="form-group">
    {!! Form::label('filling_value', 'Filling Value:') !!}
    <p>{{ $variable->filling_value }}</p>
</div>

<!-- Image Url Field -->
<div class="form-group">
    {!! Form::label('image_url', 'Image Url:') !!}
    <p>{{ $variable->image_url }}</p>
</div>

<!-- Informational Url Field -->
<div class="form-group">
    {!! Form::label('informational_url', 'Informational Url:') !!}
    <p>{{ $variable->informational_url }}</p>
</div>

<!-- Ion Icon Field -->
<div class="form-group">
    {!! Form::label('ion_icon', 'Ion Icon:') !!}
    <p>{{ $variable->ion_icon }}</p>
</div>

<!-- Kurtosis Field -->
<div class="form-group">
    {!! Form::label('kurtosis', 'Kurtosis:') !!}
    <p>{{ $variable->kurtosis }}</p>
</div>

<!-- Maximum Allowed Value Field -->
<div class="form-group">
    {!! Form::label('maximum_allowed_value', 'Maximum Allowed Value:') !!}
    <p>{{ $variable->maximum_allowed_value }}</p>
</div>

<!-- Maximum Recorded Value Field -->
<div class="form-group">
    {!! Form::label('maximum_recorded_value', 'Maximum Recorded Value:') !!}
    <p>{{ $variable->maximum_recorded_value }}</p>
</div>

<!-- Mean Field -->
<div class="form-group">
    {!! Form::label('mean', 'Mean:') !!}
    <p>{{ $variable->mean }}</p>
</div>

<!-- Median Field -->
<div class="form-group">
    {!! Form::label('median', 'Median:') !!}
    <p>{{ $variable->median }}</p>
</div>

<!-- Minimum Allowed Value Field -->
<div class="form-group">
    {!! Form::label('minimum_allowed_value', 'Minimum Allowed Value:') !!}
    <p>{{ $variable->minimum_allowed_value }}</p>
</div>

<!-- Minimum Recorded Value Field -->
<div class="form-group">
    {!! Form::label('minimum_recorded_value', 'Minimum Recorded Value:') !!}
    <p>{{ $variable->minimum_recorded_value }}</p>
</div>

<!-- Number Of Raw Measurements Field -->
<div class="form-group">
    {!! Form::label('number_of_measurements', 'Number Of Raw Measurements:') !!}
    <p>{{ $variable->number_of_measurements }}</p>
</div>

<!-- Number Of Global Variable Relationships As Cause Field -->
<div class="form-group">
    {!! Form::label('number_of_global_variable_relationships_as_cause', 'Number Of Global Variable Relationships As Cause:') !!}
    <p>{{ $variable->number_of_global_variable_relationships_as_cause }}</p>
</div>

<!-- Most Common Original Unit Id Field -->
<div class="form-group">
    {!! Form::label('most_common_original_unit_id', 'Most Common Original Unit Id:') !!}
    <p>{{ $variable->most_common_original_unit_id }}</p>
</div>

<!-- Most Common Value Field -->
<div class="form-group">
    {!! Form::label('most_common_value', 'Most Common Value:') !!}
    <p>{{ $variable->most_common_value }}</p>
</div>

<!-- Number Of Global Variable Relationships As Effect Field -->
<div class="form-group">
    {!! Form::label('number_of_global_variable_relationships_as_effect', 'Number Of Global Variable Relationships As Effect:') !!}
    <p>{{ $variable->number_of_global_variable_relationships_as_effect }}</p>
</div>

<!-- Number Of Unique Values Field -->
<div class="form-group">
    {!! Form::label('number_of_unique_values', 'Number Of Unique Values:') !!}
    <p>{{ $variable->number_of_unique_values }}</p>
</div>

<!-- Onset Delay Field -->
<div class="form-group">
    {!! Form::label('onset_delay', 'Onset Delay:') !!}
    <p>{{ $variable->onset_delay }}</p>
</div>

<!-- Outcome Field -->
<div class="form-group">
    {!! Form::label('outcome', 'Outcome:') !!}
    <p>{{ $variable->outcome }}</p>
</div>

<!-- Parent Id Field -->
<div class="form-group">
    {!! Form::label('parent_id', 'Parent Id:') !!}
    <p>{{ $variable->parent_id }}</p>
</div>

<!-- Price Field -->
<div class="form-group">
    {!! Form::label('price', 'Price:') !!}
    <p>{{ $variable->price }}</p>
</div>

<!-- Product Url Field -->
<div class="form-group">
    {!! Form::label('product_url', 'Product Url:') !!}
    <p>{{ $variable->product_url }}</p>
</div>

<!-- Second Most Common Value Field -->
<div class="form-group">
    {!! Form::label('second_most_common_value', 'Second Most Common Value:') !!}
    <p>{{ $variable->second_most_common_value }}</p>
</div>

<!-- Skewness Field -->
<div class="form-group">
    {!! Form::label('skewness', 'Skewness:') !!}
    <p>{{ $variable->skewness }}</p>
</div>

<!-- Standard Deviation Field -->
<div class="form-group">
    {!! Form::label('standard_deviation', 'Standard Deviation:') !!}
    <p>{{ $variable->standard_deviation }}</p>
</div>

<!-- Status Field -->
<div class="form-group">
    {!! Form::label('status', 'Status:') !!}
    <p>{{ $variable->status }}</p>
</div>

<!-- Third Most Common Value Field -->
<div class="form-group">
    {!! Form::label('third_most_common_value', 'Third Most Common Value:') !!}
    <p>{{ $variable->third_most_common_value }}</p>
</div>

<!-- User Id Field -->
<div class="form-group">
    {!! Form::label('user_id', 'User Id:') !!}
    <p>{{ $variable->user_id }}</p>
</div>

<!-- Variance Field -->
<div class="form-group">
    {!! Form::label('variance', 'Variance:') !!}
    <p>{{ $variable->variance }}</p>
</div>

<!-- Most Common Connector Id Field -->
<div class="form-group">
    {!! Form::label('most_common_connector_id', 'Most Common Connector Id:') !!}
    <p>{{ $variable->most_common_connector_id }}</p>
</div>

<!-- Synonyms Field -->
<div class="form-group">
    {!! Form::label('synonyms', 'Synonyms:') !!}
    <p>{{ $variable->synonyms_string() }}</p>
</div>

<!-- Wikipedia Url Field -->
<div class="form-group">
    {!! Form::label('wikipedia_url', 'Wikipedia Url:') !!}
    <p>{{ $variable->wikipedia_url }}</p>
</div>

<!-- Brand Name Field -->
<div class="form-group">
    {!! Form::label('brand_name', 'Brand Name:') !!}
    <p>{{ $variable->brand_name }}</p>
</div>

<!-- Valence Field -->
<div class="form-group">
    {!! Form::label('valence', 'Valence:') !!}
    <p>{{ $variable->valence }}</p>
</div>

<!-- Wikipedia Title Field -->
<div class="form-group">
    {!! Form::label('wikipedia_title', 'Wikipedia Title:') !!}
    <p>{{ $variable->wikipedia_title }}</p>
</div>

<!-- Number Of Tracking Reminders Field -->
<div class="form-group">
    {!! Form::label('number_of_tracking_reminders', 'Number Of Tracking Reminders:') !!}
    <p>{{ $variable->number_of_tracking_reminders }}</p>
</div>

<!-- Upc 12 Field -->
<div class="form-group">
    {!! Form::label('upc_12', 'Upc 12:') !!}
    <p>{{ $variable->upc_12 }}</p>
</div>

<!-- Upc 14 Field -->
<div class="form-group">
    {!! Form::label('upc_14', 'Upc 14:') !!}
    <p>{{ $variable->upc_14 }}</p>
</div>

<!-- Number Common Tagged By Field -->
<div class="form-group">
    {!! Form::label('number_common_tagged_by', 'Number Common Tagged By:') !!}
    <p>{{ $variable->number_common_tagged_by }}</p>
</div>

<!-- Number Of Common Tags Field -->
<div class="form-group">
    {!! Form::label('number_of_common_tags', 'Number Of Common Tags:') !!}
    <p>{{ $variable->number_of_common_tags }}</p>
</div>

<!-- Most Common Source Name Field -->
<div class="form-group">
    {!! Form::label('most_common_source_name', 'Most Common Source Name:') !!}
    <p>{{ $variable->most_common_source_name }}</p>
</div>

<!-- Data Sources Count Field -->
<div class="form-group">
    {!! Form::label('data_sources_count', 'Data Sources Count:') !!}
    <p>{{ $variable->data_sources_count_string() }}</p>
</div>

<!-- Optimal Value Message Field -->
<div class="form-group">
    {!! Form::label('optimal_value_message', 'Optimal Value Message:') !!}
    <p>{{ $variable->optimal_value_message }}</p>
</div>

<!-- Best Cause Variable Id Field -->
<div class="form-group">
    {!! Form::label('best_cause_variable_id', 'Best Cause Variable Id:') !!}
    <p>{{ $variable->best_cause_variable_id }}</p>
</div>

<!-- Best Effect Variable Id Field -->
<div class="form-group">
    {!! Form::label('best_effect_variable_id', 'Best Effect Variable Id:') !!}
    <p>{{ $variable->best_effect_variable_id }}</p>
</div>

<!-- Best Global Variable Relationship Field -->
{{--
<div class="form-group">
    {!! Form::label('best_global_variable_relationship_id', 'Best Global Variable Relationship:') !!}
    <p>{{ $variable->best_global_variable_relationship_id }}</p>
</div>
--}}


<!-- Common Maximum Allowed Daily Value Field -->
<div class="form-group">
    {!! Form::label('common_maximum_allowed_daily_value', 'Common Maximum Allowed Daily Value:') !!}
    <p>{{ $variable->common_maximum_allowed_daily_value }}</p>
</div>

<!-- Common Minimum Allowed Daily Value Field -->
<div class="form-group">
    {!! Form::label('common_minimum_allowed_daily_value', 'Common Minimum Allowed Daily Value:') !!}
    <p>{{ $variable->common_minimum_allowed_daily_value }}</p>
</div>

<!-- Common Minimum Allowed Non Zero Value Field -->
<div class="form-group">
    {!! Form::label('common_minimum_allowed_non_zero_value', 'Common Minimum Allowed Non Zero Value:') !!}
    <p>{{ $variable->common_minimum_allowed_non_zero_value }}</p>
</div>

<!-- Minimum Allowed Seconds Between Measurements Field -->
<div class="form-group">
    {!! Form::label('minimum_allowed_seconds_between_measurements', 'Minimum Allowed Seconds Between Measurements:') !!}
    <p>{{ $variable->minimum_allowed_seconds_between_measurements }}</p>
</div>

<!-- Average Seconds Between Measurements Field -->
<div class="form-group">
    {!! Form::label('average_seconds_between_measurements', 'Average Seconds Between Measurements:') !!}
    <p>{{ $variable->average_seconds_between_measurements }}</p>
</div>

<!-- Median Seconds Between Measurements Field -->
<div class="form-group">
    {!! Form::label('median_seconds_between_measurements', 'Median Seconds Between Measurements:') !!}
    <p>{{ $variable->median_seconds_between_measurements }}</p>
</div>

<!-- Number Of Raw Measurements With Tags Joins Children Field -->
<div class="form-group">
    {!! Form::label('number_of_raw_measurements_with_tags_joins_children', 'Number Of Raw Measurements With Tags Joins Children:') !!}
    <p>{{ $variable->number_of_raw_measurements_with_tags_joins_children }}</p>
</div>

<!-- Additional Meta Data Field -->
<div class="form-group">
    {!! Form::label('additional_meta_data', 'Additional Meta Data:') !!}
    <p>{{ $variable->additional_meta_data }}</p>
</div>

<!-- Manual Tracking Field -->
<div class="form-group">
    {!! Form::label('manual_tracking', 'Manual Tracking:') !!}
    <p>{{ $variable->manual_tracking }}</p>
</div>

<!-- Analysis Settings Modified At Field -->
<div class="form-group">
    {!! Form::label('analysis_settings_modified_at', 'Analysis Settings Modified At:') !!}
    <p>{{ $variable->analysis_settings_modified_at }}</p>
</div>

<!-- Newest Data At Field -->
<div class="form-group">
    {!! Form::label('newest_data_at', 'Newest Data At:') !!}
    <p>{{ $variable->newest_data_at }}</p>
</div>

<!-- Analysis Requested At Field -->
<div class="form-group">
    {!! Form::label('analysis_requested_at', 'Analysis Requested At:') !!}
    <p>{{ $variable->analysis_requested_at }}</p>
</div>

<!-- Reason For Analysis Field -->
<div class="form-group">
    {!! Form::label('reason_for_analysis', 'Reason For Analysis:') !!}
    <p>{{ $variable->reason_for_analysis }}</p>
</div>

<!-- Analysis Started At Field -->
<div class="form-group">
    {!! Form::label('analysis_started_at', 'Analysis Started At:') !!}
    <p>{{ $variable->analysis_started_at }}</p>
</div>

<!-- Analysis Ended At Field -->
<div class="form-group">
    {!! Form::label('analysis_ended_at', 'Analysis Ended At:') !!}
    <p>{{ $variable->analysis_ended_at }}</p>
</div>

<!-- User Error Message Field -->
<div class="form-group">
    {!! Form::label('user_error_message', 'User Error Message:') !!}
    <p>{{ $variable->user_error_message }}</p>
</div>

<!-- Internal Error Message Field -->
<div class="form-group">
    {!! Form::label('internal_error_message', 'Internal Error Message:') !!}
    <p>{{ $variable->internal_error_message }}</p>
</div>

<!-- Latest Tagged Measurement Start At Field -->
<div class="form-group">
    {!! Form::label('latest_tagged_measurement_start_at', 'Latest Tagged Measurement Start At:') !!}
    <p>{{ $variable->latest_tagged_measurement_start_at }}</p>
</div>

<!-- Earliest Tagged Measurement Start At Field -->
<div class="form-group">
    {!! Form::label('earliest_tagged_measurement_start_at', 'Earliest Tagged Measurement Start At:') !!}
    <p>{{ $variable->earliest_tagged_measurement_start_at }}</p>
</div>

<!-- Latest Non Tagged Measurement Start At Field -->
<div class="form-group">
    {!! Form::label('latest_non_tagged_measurement_start_at', 'Latest Non Tagged Measurement Start At:') !!}
    <p>{{ $variable->latest_non_tagged_measurement_start_at }}</p>
</div>

<!-- Earliest Non Tagged Measurement Start At Field -->
<div class="form-group">
    {!! Form::label('earliest_non_tagged_measurement_start_at', 'Earliest Non Tagged Measurement Start At:') !!}
    <p>{{ $variable->earliest_non_tagged_measurement_start_at }}</p>
</div>

<!-- Wp Post Id Field -->
<div class="form-group">
    {!! Form::label('wp_post_id', 'Wp Post Id:') !!}
    <p>{{ $variable->wp_post_id }}</p>
</div>

<!-- Number Of Soft Deleted Measurements Field -->
<div class="form-group">
    {!! Form::label('number_of_soft_deleted_measurements', 'Number Of Soft Deleted Measurements:') !!}
    <p>{{ $variable->number_of_soft_deleted_measurements }}</p>
</div>

<!-- Charts Field -->
{{-- We already get charts in show-variable.blade.php
<div class="form-group">
    {!! Form::label('charts', 'Charts:') !!}
    {!! $charts = $variable->getChartGroup()->getHtmlWithDynamicCharts(false) !!}
</div>
--}}

<!-- Creator User Id Field -->
<div class="form-group">
    {!! Form::label('creator_user_id', 'Creator User Id:') !!}
    <p>{{ $variable->creator_user_id }}</p>
</div>

<!-- Best Global Variable Relationship Id Field -->
<div class="form-group">
    {!! Form::label('best_global_variable_relationship_id', 'Best Global Variable Relationship Id:') !!}
    <p>{{ $variable->best_global_variable_relationship_id }}</p>
</div>

