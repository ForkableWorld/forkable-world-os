<?php /** @var App\Models\UserVariable $userVariable */ ?>
<!-- Parent Id Field -->
<div class="form-group">
    {!! Form::label('parent_id', 'Parent Id:') !!}
    <p>{{ $userVariable->parent_id }}</p>
</div>

<!-- Client Id Field -->
<div class="form-group">
    {!! Form::label('client_id', 'Client Id:') !!}
    <p>{{ $userVariable->client_id }}</p>
</div>

<!-- User Id Field -->
<div class="form-group">
    {!! Form::label('user_id', 'User Id:') !!}
    <p>{{ $userVariable->user_id }}</p>
</div>

<!-- Variable Id Field -->
<div class="form-group">
    {!! Form::label('variable_id', 'Variable Id:') !!}
    <p>{{ $userVariable->variable_id }}</p>
</div>

<!-- Default Unit Id Field -->
<div class="form-group">
    {!! Form::label('default_unit_id', 'Default Unit Id:') !!}
    <p>{{ $userVariable->default_unit_id }}</p>
</div>

<!-- Minimum Allowed Value Field -->
<div class="form-group">
    {!! Form::label('minimum_allowed_value', 'Minimum Allowed Value:') !!}
    <p>{{ $userVariable->minimum_allowed_value }}</p>
</div>

<!-- Maximum Allowed Value Field -->
<div class="form-group">
    {!! Form::label('maximum_allowed_value', 'Maximum Allowed Value:') !!}
    <p>{{ $userVariable->maximum_allowed_value }}</p>
</div>

<!-- Filling Value Field -->
<div class="form-group">
    {!! Form::label('filling_value', 'Filling Value:') !!}
    <p>{{ $userVariable->filling_value }}</p>
</div>

<!-- Join With Field -->
<div class="form-group">
    {!! Form::label('join_with', 'Join With:') !!}
    <p>{{ $userVariable->join_with }}</p>
</div>

<!-- Onset Delay Field -->
<div class="form-group">
    {!! Form::label('onset_delay', 'Onset Delay:') !!}
    <p>{{ $userVariable->onset_delay }}</p>
</div>

<!-- Duration Of Action Field -->
<div class="form-group">
    {!! Form::label('duration_of_action', 'Duration Of Action:') !!}
    <p>{{ $userVariable->duration_of_action }}</p>
</div>

<!-- Variable Category Id Field -->
<div class="form-group">
    {!! Form::label('variable_category_id', 'Variable Category Id:') !!}
    <p>{{ $userVariable->variable_category_id }}</p>
</div>

<!-- Public Field -->
<div class="form-group">
    {!! Form::label('is_public', 'Public:') !!}
    <p>{{ $userVariable->is_public }}</p>
</div>

<!-- Cause Only Field -->
<div class="form-group">
    {!! Form::label('cause_only', 'Cause Only:') !!}
    <p>{{ $userVariable->cause_only }}</p>
</div>

<!-- Filling Type Field -->
<div class="form-group">
    {!! Form::label('filling_type', 'Filling Type:') !!}
    <p>{{ $userVariable->filling_type }}</p>
</div>

<!-- Number Of Raw Measurements Field -->
<div class="form-group">
    {!! Form::label('number_of_measurements', 'Number Of Measurements:') !!}
    <p>{{ $userVariable->number_of_measurements }}</p>
</div>

<!-- Number Of Processed Daily Measurements Field -->
<div class="form-group">
    {!! Form::label('number_of_processed_daily_measurements', 'Number Of Processed Daily Measurements:') !!}
    <p>{{ $userVariable->number_of_processed_daily_measurements }}</p>
</div>

<!-- Measurements At Last Analysis Field -->
<div class="form-group">
    {!! Form::label('measurements_at_last_analysis', 'Measurements At Last Analysis:') !!}
    <p>{{ $userVariable->measurements_at_last_analysis }}</p>
</div>

<!-- Last Unit Id Field -->
<div class="form-group">
    {!! Form::label('last_unit_id', 'Last Unit Id:') !!}
    <p>{{ $userVariable->last_unit_id }}</p>
</div>

<!-- Last Original Unit Id Field -->
<div class="form-group">
    {!! Form::label('last_original_unit_id', 'Last Original Unit Id:') !!}
    <p>{{ $userVariable->last_original_unit_id }}</p>
</div>

<!-- Last Value Field -->
<div class="form-group">
    {!! Form::label('last_value', 'Last Value:') !!}
    <p>{{ $userVariable->last_value }}</p>
</div>

<!-- Last Original Value Field -->
<div class="form-group">
    {!! Form::label('last_original_value', 'Last Original Value:') !!}
    <p>{{ $userVariable->last_original_value }}</p>
</div>


<!-- Number Of VariableRelationships Field -->
<div class="form-group">
    {!! Form::label('number_of_correlations', 'Number Of VariableRelationships:') !!}
    <p>{{ $userVariable->number_of_correlations }}</p>
</div>

<!-- Status Field -->
<div class="form-group">
    {!! Form::label('status', 'Status:') !!}
    <p>{{ $userVariable->status }}</p>
</div>

<!-- Standard Deviation Field -->
<div class="form-group">
    {!! Form::label('standard_deviation', 'Standard Deviation:') !!}
    <p>{{ $userVariable->standard_deviation }}</p>
</div>

<!-- Variance Field -->
<div class="form-group">
    {!! Form::label('variance', 'Variance:') !!}
    <p>{{ $userVariable->variance }}</p>
</div>

<!-- Minimum Recorded Value Field -->
<div class="form-group">
    {!! Form::label('minimum_recorded_value', 'Minimum Recorded Value:') !!}
    <p>{{ $userVariable->minimum_recorded_value }}</p>
</div>

<!-- Maximum Recorded Value Field -->
<div class="form-group">
    {!! Form::label('maximum_recorded_value', 'Maximum Recorded Value:') !!}
    <p>{{ $userVariable->maximum_recorded_value }}</p>
</div>

<!-- Mean Field -->
<div class="form-group">
    {!! Form::label('mean', 'Mean:') !!}
    <p>{{ $userVariable->mean }}</p>
</div>

<!-- Median Field -->
<div class="form-group">
    {!! Form::label('median', 'Median:') !!}
    <p>{{ $userVariable->median }}</p>
</div>

<!-- Most Common Original Unit Id Field -->
<div class="form-group">
    {!! Form::label('most_common_original_unit_id', 'Most Common Original Unit Id:') !!}
    <p>{{ $userVariable->most_common_original_unit_id }}</p>
</div>

<!-- Most Common Value Field -->
<div class="form-group">
    {!! Form::label('most_common_value', 'Most Common Value:') !!}
    <p>{{ $userVariable->most_common_value }}</p>
</div>

<!-- Number Of Unique Daily Values Field -->
<div class="form-group">
    {!! Form::label('number_of_unique_daily_values', 'Number Of Unique Daily Values:') !!}
    <p>{{ $userVariable->number_of_unique_daily_values }}</p>
</div>

<!-- Number Of Unique Values Field -->
<div class="form-group">
    {!! Form::label('number_of_unique_values', 'Number Of Unique Values:') !!}
    <p>{{ $userVariable->number_of_unique_values }}</p>
</div>

<!-- Number Of Changes Field -->
<div class="form-group">
    {!! Form::label('number_of_changes', 'Number Of Changes:') !!}
    <p>{{ $userVariable->number_of_changes }}</p>
</div>

<!-- Skewness Field -->
<div class="form-group">
    {!! Form::label('skewness', 'Skewness:') !!}
    <p>{{ $userVariable->skewness }}</p>
</div>

<!-- Kurtosis Field -->
<div class="form-group">
    {!! Form::label('kurtosis', 'Kurtosis:') !!}
    <p>{{ $userVariable->kurtosis }}</p>
</div>

<!-- Latitude Field -->
<div class="form-group">
    {!! Form::label('latitude', 'Latitude:') !!}
    <p>{{ $userVariable->latitude }}</p>
</div>

<!-- Longitude Field -->
<div class="form-group">
    {!! Form::label('longitude', 'Longitude:') !!}
    <p>{{ $userVariable->longitude }}</p>
</div>

<!-- Location Field -->
<div class="form-group">
    {!! Form::label('location', 'Location:') !!}
    <p>{{ $userVariable->location }}</p>
</div>

<!-- Outcome Field -->
<div class="form-group">
    {!! Form::label('outcome', 'Outcome:') !!}
    <p>{{ $userVariable->outcome }}</p>
</div>

<!-- Data Sources Count Field -->
<div class="form-group">
    {!! Form::label('data_sources_count', 'Data Sources Count:') !!}
    <p>{{ \App\Logging\QMLog::print_r($userVariable->data_sources_count, true) }}</p>
</div>

<!-- Earliest Filling Time Field -->
<div class="form-group">
    {!! Form::label('earliest_filling_time', 'Earliest Filling Time:') !!}
    <p>{{ $userVariable->earliest_filling_time }}</p>
</div>

<!-- Latest Filling Time Field -->
<div class="form-group">
    {!! Form::label('latest_filling_time', 'Latest Filling Time:') !!}
    <p>{{ $userVariable->latest_filling_time }}</p>
</div>

<!-- Last Processed Daily Value Field -->
<div class="form-group">
    {!! Form::label('last_processed_daily_value', 'Last Processed Daily Value:') !!}
    <p>{{ $userVariable->last_processed_daily_value }}</p>
</div>

<!-- Outcome Of Interest Field -->
<div class="form-group">
    {!! Form::label('outcome_of_interest', 'Outcome Of Interest:') !!}
    <p>{{ $userVariable->outcome_of_interest }}</p>
</div>

<!-- Predictor Of Interest Field -->
<div class="form-group">
    {!! Form::label('predictor_of_interest', 'Predictor Of Interest:') !!}
    <p>{{ $userVariable->predictor_of_interest }}</p>
</div>

<!-- Experiment Start Time Field -->
<div class="form-group">
    {!! Form::label('experiment_start_time', 'Experiment Start Time:') !!}
    <p>{{ $userVariable->experiment_start_time }}</p>
</div>

<!-- Experiment End Time Field -->
<div class="form-group">
    {!! Form::label('experiment_end_time', 'Experiment End Time:') !!}
    <p>{{ $userVariable->experiment_end_time }}</p>
</div>

<!-- Description Field -->
<div class="form-group">
    {!! Form::label('description', 'Description:') !!}
    <p>{{ $userVariable->description }}</p>
</div>

<!-- Alias Field -->
<div class="form-group">
    {!! Form::label('alias', 'Alias:') !!}
    <p>{{ $userVariable->alias }}</p>
</div>

<!-- Second To Last Value Field -->
<div class="form-group">
    {!! Form::label('second_to_last_value', 'Second To Last Value:') !!}
    <p>{{ $userVariable->second_to_last_value }}</p>
</div>

<!-- Third To Last Value Field -->
<div class="form-group">
    {!! Form::label('third_to_last_value', 'Third To Last Value:') !!}
    <p>{{ $userVariable->third_to_last_value }}</p>
</div>

<!-- Number Of User Variable Relationships As Effect Field -->
<div class="form-group">
    {!! Form::label('number_of_user_variable_relationships_as_effect', 'Number Of User Variable Relationships As Effect:') !!}
    <p>{{ $userVariable->number_of_user_variable_relationships_as_effect }}</p>
</div>

<!-- Number Of User Variable Relationships As Cause Field -->
<div class="form-group">
    {!! Form::label('number_of_user_variable_relationships_as_cause', 'Number Of User Variable Relationships As Cause:') !!}
    <p>{{ $userVariable->number_of_user_variable_relationships_as_cause }}</p>
</div>

<!-- Combination Operation Field -->
<div class="form-group">
    {!! Form::label('combination_operation', 'Combination Operation:') !!}
    <p>{{ $userVariable->combination_operation }}</p>
</div>

<!-- Share User Measurements Field -->
<div class="form-group">
    {!! Form::label('is_public', 'Share User Measurements:') !!}
    <p>{{ $userVariable->is_public }}</p>
</div>

<!-- Informational Url Field -->
<div class="form-group">
    {!! Form::label('informational_url', 'Informational Url:') !!}
    <p>{{ $userVariable->informational_url }}</p>
</div>

<!-- Most Common Connector Id Field -->
<div class="form-group">
    {!! Form::label('most_common_connector_id', 'Most Common Connector Id:') !!}
    <p>{{ $userVariable->most_common_connector_id }}</p>
</div>

<!-- Valence Field -->
<div class="form-group">
    {!! Form::label('valence', 'Valence:') !!}
    <p>{{ $userVariable->valence }}</p>
</div>

<!-- Wikipedia Title Field -->
<div class="form-group">
    {!! Form::label('wikipedia_title', 'Wikipedia Title:') !!}
    <p>{{ $userVariable->wikipedia_title }}</p>
</div>

<!-- Number Of Tracking Reminders Field -->
<div class="form-group">
    {!! Form::label('number_of_tracking_reminders', 'Number Of Tracking Reminders:') !!}
    <p>{{ $userVariable->number_of_tracking_reminders }}</p>
</div>

<!-- Number Of Raw Measurements With Tags Joins Children Field -->
<div class="form-group">
    {!! Form::label('number_of_raw_measurements_with_tags_joins_children', 'Number Of Raw Measurements With Tags Joins Children:') !!}
    <p>{{ $userVariable->number_of_raw_measurements_with_tags_joins_children }}</p>
</div>

<!-- Most Common Source Name Field -->
<div class="form-group">
    {!! Form::label('most_common_source_name', 'Most Common Source Name:') !!}
    <p>{{ $userVariable->most_common_source_name }}</p>
</div>

<!-- Optimal Value Message Field -->
<div class="form-group">
    {!! Form::label('optimal_value_message', 'Optimal Value Message:') !!}
    <p>{{ $userVariable->optimal_value_message }}</p>
</div>

<!-- Best Cause Variable Id Field -->
<div class="form-group">
    {!! Form::label('best_cause_variable_id', 'Best Cause Variable Id:') !!}
    <p>{{ $userVariable->best_cause_variable_id }}</p>
</div>

<!-- Best Effect Variable Id Field -->
<div class="form-group">
    {!! Form::label('best_effect_variable_id', 'Best Effect Variable Id:') !!}
    <p>{{ $userVariable->best_effect_variable_id }}</p>
</div>

<!-- Best User Variable Relationship Field -->
<div class="form-group">
    {!! Form::label('best_user_variable_relationship_id', 'Best User Variable Relationship:') !!}
    <p>{{ $userVariable->best_user_variable_relationship_id }}</p>
    {!! Form::label('best_user_variable_relationship', 'Best User Variable Relationship:') !!}
    {!! $userVariable->getBestUserVariableRelationshipLink() !!}
</div>

<!-- User Maximum Allowed Daily Value Field -->
<div class="form-group">
    {!! Form::label('user_maximum_allowed_daily_value', 'User Maximum Allowed Daily Value:') !!}
    <p>{{ $userVariable->user_maximum_allowed_daily_value }}</p>
</div>

<!-- User Minimum Allowed Daily Value Field -->
<div class="form-group">
    {!! Form::label('user_minimum_allowed_daily_value', 'User Minimum Allowed Daily Value:') !!}
    <p>{{ $userVariable->user_minimum_allowed_daily_value }}</p>
</div>

<!-- User Minimum Allowed Non Zero Value Field -->
<div class="form-group">
    {!! Form::label('user_minimum_allowed_non_zero_value', 'User Minimum Allowed Non Zero Value:') !!}
    <p>{{ $userVariable->user_minimum_allowed_non_zero_value }}</p>
</div>

<!-- Minimum Allowed Seconds Between Measurements Field -->
<div class="form-group">
    {!! Form::label('minimum_allowed_seconds_between_measurements', 'Minimum Allowed Seconds Between Measurements:') !!}
    <p>{{ $userVariable->minimum_allowed_seconds_between_measurements }}</p>
</div>

<!-- Average Seconds Between Measurements Field -->
<div class="form-group">
    {!! Form::label('average_seconds_between_measurements', 'Average Seconds Between Measurements:') !!}
    <p>{{ $userVariable->average_seconds_between_measurements }}</p>
</div>

<!-- Median Seconds Between Measurements Field -->
<div class="form-group">
    {!! Form::label('median_seconds_between_measurements', 'Median Seconds Between Measurements:') !!}
    <p>{{ $userVariable->median_seconds_between_measurements }}</p>
</div>


<!-- Last Correlated At Field -->
<div class="form-group">
    {!! Form::label('last_correlated_at', 'Last Correlated At:') !!}
    <p>{{ $userVariable->last_correlated_at }}</p>
</div>

<!-- Number Of Measurements With Tags At Last UserVariableRelationship Field -->
<div class="form-group">
    {!! Form::label('number_of_measurements_with_tags_at_last_correlation', 'Number Of Measurements With Tags At Last UserVariableRelationship:') !!}
    <p>{{ $userVariable->number_of_measurements_with_tags_at_last_correlation }}</p>
</div>

<!-- Analysis Settings Modified At Field -->
<div class="form-group">
    {!! Form::label('analysis_settings_modified_at', 'Analysis Settings Modified At:') !!}
    <p>{{ $userVariable->analysis_settings_modified_at }}</p>
</div>

<!-- Newest Data At Field -->
<div class="form-group">
    {!! Form::label('newest_data_at', 'Newest Data At:') !!}
    <p>{{ $userVariable->newest_data_at }}</p>
</div>

<!-- Analysis Requested At Field -->
<div class="form-group">
    {!! Form::label('analysis_requested_at', 'Analysis Requested At:') !!}
    <p>{{ $userVariable->analysis_requested_at }}</p>
</div>

<!-- Reason For Analysis Field -->
<div class="form-group">
    {!! Form::label('reason_for_analysis', 'Reason For Analysis:') !!}
    <p>{{ $userVariable->reason_for_analysis }}</p>
</div>

<!-- Analysis Started At Field -->
<div class="form-group">
    {!! Form::label('analysis_started_at', 'Analysis Started At:') !!}
    <p>{{ $userVariable->analysis_started_at }}</p>
</div>

<!-- Analysis Ended At Field -->
<div class="form-group">
    {!! Form::label('analysis_ended_at', 'Analysis Ended At:') !!}
    <p>{{ $userVariable->analysis_ended_at }}</p>
</div>

<!-- User Error Message Field -->
<div class="form-group">
    {!! Form::label('user_error_message', 'User Error Message:') !!}
    <p>{{ $userVariable->user_error_message }}</p>
</div>

<!-- Internal Error Message Field -->
<div class="form-group">
    {!! Form::label('internal_error_message', 'Internal Error Message:') !!}
    <p>{{ $userVariable->internal_error_message }}</p>
</div>

<!-- Earliest Source Measurement Start At Field -->
<div class="form-group">
    {!! Form::label('earliest_source_measurement_start_at', 'Earliest Source Measurement Start At:') !!}
    <p>{{ $userVariable->earliest_source_measurement_start_at }}</p>
</div>

<!-- Latest Source Measurement Start At Field -->
<div class="form-group">
    {!! Form::label('latest_source_measurement_start_at', 'Latest Source Measurement Start At:') !!}
    <p>{{ $userVariable->latest_source_measurement_start_at }}</p>
</div>

<!-- Latest Tagged Measurement Start At Field -->
<div class="form-group">
    {!! Form::label('latest_tagged_measurement_start_at', 'Latest Tagged Measurement Start At:') !!}
    <p>{{ $userVariable->latest_tagged_measurement_start_at }}</p>
</div>

<!-- Earliest Tagged Measurement Start At Field -->
<div class="form-group">
    {!! Form::label('earliest_tagged_measurement_start_at', 'Earliest Tagged Measurement Start At:') !!}
    <p>{{ $userVariable->earliest_tagged_measurement_start_at }}</p>
</div>

<!-- Latest Non Tagged Measurement Start At Field -->
<div class="form-group">
    {!! Form::label('latest_non_tagged_measurement_start_at', 'Latest Non Tagged Measurement Start At:') !!}
    <p>{{ $userVariable->latest_non_tagged_measurement_start_at }}</p>
</div>

<!-- Earliest Non Tagged Measurement Start At Field -->
<div class="form-group">
    {!! Form::label('earliest_non_tagged_measurement_start_at', 'Earliest Non Tagged Measurement Start At:') !!}
    <p>{{ $userVariable->earliest_non_tagged_measurement_start_at }}</p>
</div>

<!-- Wp Post Id Field -->
<div class="form-group">
    {!! Form::label('wp_post_id', 'Wp Post Id:') !!}
    <p>{{ $userVariable->wp_post_id }}</p>
</div>

<!-- Number Of Soft Deleted Measurements Field -->
<div class="form-group">
    {!! Form::label('number_of_soft_deleted_measurements', 'Number Of Soft Deleted Measurements:') !!}
    <p>{{ $userVariable->number_of_soft_deleted_measurements }}</p>
</div>

{{--
<!-- Charts Field -->
<div class="form-group">
    {!! Form::label('charts', 'Charts:') !!}
    {!! $userVariable->getChartGroup()->getHtmlWithDynamicCharts(false) !!}
</div>
--}}


<!-- Best User Variable Relationship Id Field -->
<div class="form-group">
    {!! Form::label('best_user_variable_relationship_id', 'Best User Variable Relationship Id:') !!}
    <p>{{ $userVariable->best_user_variable_relationship_id }}</p>
</div>

