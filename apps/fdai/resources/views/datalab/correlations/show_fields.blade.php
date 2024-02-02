<?php /** @var App\Models\UserVariableRelationship $correlation */ ?>

{!! $correlation->getInterestingRelationshipsMenu()->getMaterialStatCards() !!}

<!-- Qm Score Field -->
<div class="form-group">
    {!! Form::label('qm_score', 'QM Score: ') !!} {{ $correlation->qm_score }}
</div>

<!-- Forward Pearson UserVariableRelationship Coefficient Field -->
<div class="form-group">
    {!! Form::label('forward_pearson_correlation_coefficient', 'Forward Pearson Correlation Coefficient:') !!}
    <p>{{ $correlation->forward_pearson_correlation_coefficient }}</p>
</div>

<!-- Value Predicting High Outcome Field -->
<div class="form-group">
    {!! Form::label('value_predicting_high_outcome', 'Value Predicting High Outcome:') !!}
    <p>{{ $correlation->value_predicting_high_outcome }}</p>
</div>

<!-- Value Predicting Low Outcome Field -->
<div class="form-group">
    {!! Form::label('value_predicting_low_outcome', 'Value Predicting Low Outcome:') !!}
    <p>{{ $correlation->value_predicting_low_outcome }}</p>
</div>

<!-- Predicts High Effect Change Field -->
<div class="form-group">
    {!! Form::label('predicts_high_effect_change', 'Predicts High Effect Change:') !!}
    <p>{{ $correlation->predicts_high_effect_change }}</p>
</div>

<!-- Predicts Low Effect Change Field -->
<div class="form-group">
    {!! Form::label('predicts_low_effect_change', 'Predicts Low Effect Change:') !!}
    <p>{{ $correlation->predicts_low_effect_change }}</p>
</div>

<!-- Average Effect Field -->
<div class="form-group">
    {!! Form::label('average_effect', 'Average Effect:') !!}
    <p>{{ $correlation->average_effect }}</p>
</div>

<!-- Average Effect Following High Cause Field -->
<div class="form-group">
    {!! Form::label('average_effect_following_high_cause', 'Average Effect Following High Cause:') !!}
    <p>{{ $correlation->average_effect_following_high_cause }}</p>
</div>

<!-- Average Effect Following Low Cause Field -->
<div class="form-group">
    {!! Form::label('average_effect_following_low_cause', 'Average Effect Following Low Cause:') !!}
    <p>{{ $correlation->average_effect_following_low_cause }}</p>
</div>

<!-- Average Daily Low Cause Field -->
<div class="form-group">
    {!! Form::label('average_daily_low_cause', 'Average Daily Low Cause:') !!}
    <p>{{ $correlation->average_daily_low_cause }}</p>
</div>

<!-- Average Daily High Cause Field -->
<div class="form-group">
    {!! Form::label('average_daily_high_cause', 'Average Daily High Cause:') !!}
    <p>{{ $correlation->average_daily_high_cause }}</p>
</div>

<!-- Average Forward Pearson UserVariableRelationship Over Onset Delays Field -->
<div class="form-group">
    {!! Form::label('average_forward_pearson_correlation_over_onset_delays', 'Average Forward Pearson User Variable Relationship Over Onset Delays:') !!}
    <p>{{ $correlation->average_forward_pearson_correlation_over_onset_delays }}</p>
</div>

<!-- Average Reverse Pearson UserVariableRelationship Over Onset Delays Field -->
<div class="form-group">
    {!! Form::label('average_reverse_pearson_correlation_over_onset_delays', 'Average Reverse Pearson User Variable Relationship Over Onset Delays:') !!}
    <p>{{ $correlation->average_reverse_pearson_correlation_over_onset_delays }}</p>
</div>

<!-- Cause Changes Field -->
<div class="form-group">
    {!! Form::label('cause_changes', 'Cause Changes:') !!}
    <p>{{ $correlation->cause_changes }}</p>
</div>

<!-- Cause Filling Value Field -->
<div class="form-group">
    {!! Form::label('cause_filling_value', 'Cause Filling Value:') !!}
    <p>{{ $correlation->cause_filling_value }}</p>
</div>

<!-- Cause Number Of Processed Daily Measurements Field -->
<div class="form-group">
    {!! Form::label('cause_number_of_processed_daily_measurements', 'Cause Number Of Processed Daily Measurements:') !!}
    <p>{{ $correlation->cause_number_of_processed_daily_measurements }}</p>
</div>

<!-- Cause Number Of Raw Measurements Field -->
<div class="form-group">
    {!! Form::label('cause_number_of_raw_measurements', 'Cause Number Of Raw Measurements:') !!}
    <p>{{ $correlation->cause_number_of_raw_measurements }}</p>
</div>

<!-- Cause Unit Id Field -->
<div class="form-group">
    {!! Form::label('cause_unit_id', 'Cause Unit Id:') !!}
    <p>{{ $correlation->cause_unit_id }}</p>
</div>

<!-- Confidence Interval Field -->
<div class="form-group">
    {!! Form::label('confidence_interval', 'Confidence Interval:') !!}
    <p>{{ $correlation->confidence_interval }}</p>
</div>

<!-- Critical T Value Field -->
<div class="form-group">
    {!! Form::label('critical_t_value', 'Critical T Value:') !!}
    <p>{{ $correlation->critical_t_value }}</p>
</div>

<!-- Data Source Name Field -->
<div class="form-group">
    {!! Form::label('data_source_name', 'Data Source Name:') !!}
    <p>{{ $correlation->data_source_name }}</p>
</div>

<!-- Duration Of Action Field -->
<div class="form-group">
    {!! Form::label('duration_of_action', 'Duration Of Action:') !!}
    <p>{{ $correlation->duration_of_action }}</p>
</div>

<!-- Effect Changes Field -->
<div class="form-group">
    {!! Form::label('effect_changes', 'Effect Changes:') !!}
    <p>{{ $correlation->effect_changes }}</p>
</div>

<!-- Effect Filling Value Field -->
<div class="form-group">
    {!! Form::label('effect_filling_value', 'Effect Filling Value:') !!}
    <p>{{ $correlation->effect_filling_value }}</p>
</div>

<!-- Effect Number Of Processed Daily Measurements Field -->
<div class="form-group">
    {!! Form::label('effect_number_of_processed_daily_measurements', 'Effect Number Of Processed Daily Measurements:') !!}
    <p>{{ $correlation->effect_number_of_processed_daily_measurements }}</p>
</div>

<!-- Effect Number Of Raw Measurements Field -->
<div class="form-group">
    {!! Form::label('effect_number_of_raw_measurements', 'Effect Number Of Raw Measurements:') !!}
    <p>{{ $correlation->effect_number_of_raw_measurements }}</p>
</div>

<!-- Error Field -->
<div class="form-group">
    {!! Form::label('error', 'Error:') !!}
    <p>{{ $correlation->error }}</p>
</div>

<!-- Forward Spearman UserVariableRelationship Coefficient Field -->
<div class="form-group">
    {!! Form::label('forward_spearman_correlation_coefficient', 'Forward Spearman Correlation Coefficient:') !!}
    <p>{{ $correlation->forward_spearman_correlation_coefficient }}</p>
</div>

<!-- Number Of Days Field -->
<div class="form-group">
    {!! Form::label('number_of_days', 'Number Of Days:') !!}
    <p>{{ $correlation->number_of_days }}</p>
</div>

<!-- Number Of Pairs Field -->
<div class="form-group">
    {!! Form::label('number_of_pairs', 'Number Of Pairs:') !!}
    <p>{{ $correlation->number_of_pairs }}</p>
</div>

<!-- Onset Delay Field -->
<div class="form-group">
    {!! Form::label('onset_delay', 'Onset Delay:') !!}
    <p>{{ $correlation->onset_delay }}</p>
</div>

<!-- Onset Delay With Strongest Pearson UserVariableRelationship Field -->
<div class="form-group">
    {!! Form::label('onset_delay_with_strongest_pearson_correlation', 'Onset Delay With Strongest Pearson UserVariableRelationship:') !!}
    <p>{{ $correlation->onset_delay_with_strongest_pearson_correlation }}</p>
</div>

<!-- Optimal Pearson Product Field -->
<div class="form-group">
    {!! Form::label('optimal_pearson_product', 'Optimal Pearson Product:') !!}
    <p>{{ $correlation->optimal_pearson_product }}</p>
</div>

<!-- P Value Field -->
<div class="form-group">
    {!! Form::label('p_value', 'P Value:') !!}
    <p>{{ $correlation->p_value }}</p>
</div>

<!-- Pearson UserVariableRelationship With No Onset Delay Field -->
<div class="form-group">
    {!! Form::label('pearson_correlation_with_no_onset_delay', 'Pearson User Variable Relationship With No Onset Delay:') !!}
    <p>{{ $correlation->pearson_correlation_with_no_onset_delay }}</p>
</div>

<!-- Predictive Pearson UserVariableRelationship Coefficient Field -->
<div class="form-group">
    {!! Form::label('predictive_pearson_correlation_coefficient', 'Predictive Pearson User Variable Relationship Coefficient:') !!}
    <p>{{ $correlation->predictive_pearson_correlation_coefficient }}</p>
</div>

<!-- Reverse Pearson UserVariableRelationship Coefficient Field -->
<div class="form-group">
    {!! Form::label('reverse_pearson_correlation_coefficient', 'Reverse Pearson User Variable Relationship Coefficient:') !!}
    <p>{{ $correlation->reverse_pearson_correlation_coefficient }}</p>
</div>

<!-- Statistical Significance Field -->
<div class="form-group">
    {!! Form::label('statistical_significance', 'Statistical Significance:') !!}
    <p>{{ $correlation->statistical_significance }}</p>
</div>

<!-- Strongest Pearson UserVariableRelationship Coefficient Field -->
<div class="form-group">
    {!! Form::label('strongest_pearson_correlation_coefficient', 'Strongest Pearson Correlation Coefficient:') !!}
    <p>{{ $correlation->strongest_pearson_correlation_coefficient }}</p>
</div>

<!-- T Value Field -->
<div class="form-group">
    {!! Form::label('t_value', 'T Value:') !!}
    <p>{{ $correlation->t_value }}</p>
</div>

<!-- User Id Field -->
<div class="form-group">
    {!! Form::label('user_id', 'User Id:') !!}
    <p>{{ $correlation->user_id }}</p>
</div>

<!-- Grouped Cause Value Closest To Value Predicting Low Outcome Field -->
<div class="form-group">
    {!! Form::label('grouped_cause_value_closest_to_value_predicting_low_outcome', 'Grouped Cause Value Closest To Value Predicting Low Outcome:') !!}
    <p>{{ $correlation->grouped_cause_value_closest_to_value_predicting_low_outcome }}</p>
</div>

<!-- Grouped Cause Value Closest To Value Predicting High Outcome Field -->
<div class="form-group">
    {!! Form::label('grouped_cause_value_closest_to_value_predicting_high_outcome', 'Grouped Cause Value Closest To Value Predicting High Outcome:') !!}
    <p>{{ $correlation->grouped_cause_value_closest_to_value_predicting_high_outcome }}</p>
</div>

<!-- Client Id Field -->
<div class="form-group">
    {!! Form::label('client_id', 'Client Id:') !!}
    <p>{{ $correlation->client_id }}</p>
</div>

<!-- Published At Field -->
<div class="form-group">
    {!! Form::label('published_at', 'Published At:') !!}
    <p>{{ $correlation->published_at }}</p>
</div>

<!-- Wp Post Id Field -->
<div class="form-group">
    {!! Form::label('wp_post_id', 'Wp Post Id:') !!}
    <p>{{ $correlation->wp_post_id }}</p>
</div>

<!-- Status Field -->
<div class="form-group">
    {!! Form::label('status', 'Status:') !!}
    <p>{{ $correlation->status }}</p>
</div>

<!-- Cause Variable Category Id Field -->
<div class="form-group">
    {!! Form::label('cause_variable_category_id', 'Cause Variable Category Id:') !!}
    <p>{{ $correlation->cause_variable_category_id }}</p>
</div>

<!-- Effect Variable Category Id Field -->
<div class="form-group">
    {!! Form::label('effect_variable_category_id', 'Effect Variable Category Id:') !!}
    <p>{{ $correlation->effect_variable_category_id }}</p>
</div>

<!-- Interesting Variable Category Pair Field -->
<div class="form-group">
    {!! Form::label('interesting_variable_category_pair', 'Interesting Variable Category Pair:') !!}
    <p>{{ $correlation->interesting_variable_category_pair }}</p>
</div>

<!-- Newest Data At Field -->
<div class="form-group">
    {!! Form::label('newest_data_at', 'Newest Data At:') !!}
    <p>{{ $correlation->newest_data_at }}</p>
</div>

<!-- Analysis Requested At Field -->
<div class="form-group">
    {!! Form::label('analysis_requested_at', 'Analysis Requested At:') !!}
    <p>{{ $correlation->analysis_requested_at }}</p>
</div>

<!-- Reason For Analysis Field -->
<div class="form-group">
    {!! Form::label('reason_for_analysis', 'Reason For Analysis:') !!}
    <p>{{ $correlation->reason_for_analysis }}</p>
</div>

<!-- Analysis Started At Field -->
<div class="form-group">
    {!! Form::label('analysis_started_at', 'Analysis Started At:') !!}
    <p>{{ $correlation->analysis_started_at }}</p>
</div>

<!-- Analysis Ended At Field -->
<div class="form-group">
    {!! Form::label('analysis_ended_at', 'Analysis Ended At:') !!}
    <p>{{ $correlation->analysis_ended_at }}</p>
</div>

<!-- User Error Message Field -->
<div class="form-group">
    {!! Form::label('user_error_message', 'User Error Message:') !!}
    <p>{{ $correlation->user_error_message }}</p>
</div>

<!-- Internal Error Message Field -->
<div class="form-group">
    {!! Form::label('internal_error_message', 'Internal Error Message:') !!}
    <p>{{ $correlation->internal_error_message }}</p>
</div>

<!-- Cause User Variable Id Field -->
<div class="form-group">
    {!! Form::label('cause_user_variable_id', 'Cause User Variable Id:') !!}
    <p>{{ $correlation->cause_user_variable_id }}</p>
</div>

<!-- Effect User Variable Id Field -->
<div class="form-group">
    {!! Form::label('effect_user_variable_id', 'Effect User Variable Id:') !!}
    <p>{{ $correlation->effect_user_variable_id }}</p>
</div>

<!-- Latest Measurement Start At Field -->
<div class="form-group">
    {!! Form::label('latest_measurement_start_at', 'Latest Measurement Start At:') !!}
    <p>{{ $correlation->latest_measurement_start_at }}</p>
</div>

<!-- Earliest Measurement Start At Field -->
<div class="form-group">
    {!! Form::label('earliest_measurement_start_at', 'Earliest Measurement Start At:') !!}
    <p>{{ $correlation->earliest_measurement_start_at }}</p>
</div>

<!-- Cause Variable Id Field -->
<div class="form-group">
    {!! Form::label('cause_variable_id', 'Cause Variable Id:') !!}
    <p>{{ $correlation->cause_variable_id }}</p>
</div>

<!-- Effect Variable Id Field -->
<div class="form-group">
    {!! Form::label('effect_variable_id', 'Effect Variable Id:') !!}
    <p>{{ $correlation->effect_variable_id }}</p>
</div>

<!-- Cause Baseline Average Per Day Field -->
<div class="form-group">
    {!! Form::label('cause_baseline_average_per_day', 'Cause Baseline Average Per Day:') !!}
    <p>{{ $correlation->cause_baseline_average_per_day }}</p>
</div>

<!-- Cause Baseline Average Per Duration Of Action Field -->
<div class="form-group">
    {!! Form::label('cause_baseline_average_per_duration_of_action', 'Cause Baseline Average Per Duration Of Action:') !!}
    <p>{{ $correlation->cause_baseline_average_per_duration_of_action }}</p>
</div>

<!-- Cause Treatment Average Per Day Field -->
<div class="form-group">
    {!! Form::label('cause_treatment_average_per_day', 'Cause Treatment Average Per Day:') !!}
    <p>{{ $correlation->cause_treatment_average_per_day }}</p>
</div>

<!-- Cause Treatment Average Per Duration Of Action Field -->
<div class="form-group">
    {!! Form::label('cause_treatment_average_per_duration_of_action', 'Cause Treatment Average Per Duration Of Action:') !!}
    <p>{{ $correlation->cause_treatment_average_per_duration_of_action }}</p>
</div>

<!-- Effect Baseline Average Field -->
<div class="form-group">
    {!! Form::label('effect_baseline_average', 'Effect Baseline Average:') !!}
    <p>{{ $correlation->effect_baseline_average }}</p>
</div>

<!-- Effect Baseline Relative Standard Deviation Field -->
<div class="form-group">
    {!! Form::label('effect_baseline_relative_standard_deviation', 'Effect Baseline Relative Standard Deviation:') !!}
    <p>{{ $correlation->effect_baseline_relative_standard_deviation }}</p>
</div>

<!-- Effect Baseline Standard Deviation Field -->
<div class="form-group">
    {!! Form::label('effect_baseline_standard_deviation', 'Effect Baseline Standard Deviation:') !!}
    <p>{{ $correlation->effect_baseline_standard_deviation }}</p>
</div>

<!-- Effect Follow Up Average Field -->
<div class="form-group">
    {!! Form::label('effect_follow_up_average', 'Effect Follow Up Average:') !!}
    <p>{{ $correlation->effect_follow_up_average }}</p>
</div>

<!-- Effect Follow Up Percent Change From Baseline Field -->
<div class="form-group">
    {!! Form::label('effect_follow_up_percent_change_from_baseline', 'Effect Follow Up Percent Change From Baseline:') !!}
    <p>{{ $correlation->effect_follow_up_percent_change_from_baseline }}</p>
</div>

<!-- Z Score Field -->
<div class="form-group">
    {!! Form::label('z_score', 'Z Score:') !!}
    <p>{{ $correlation->z_score }}</p>
</div>

{{--
<!-- Charts Field -->
<div class="form-group">
    {!! Form::label('charts', 'Charts:') !!}
    {!! $correlation->getChartGroup()->getChartHtmlWithEmbeddedImages() !!}
</div>
--}}

<!-- Experiment End At Field -->
<div class="form-group">
    {!! Form::label('experiment_end_at', 'Experiment End At:') !!}
    <p>{{ $correlation->experiment_end_at }}</p>
</div>

<!-- Experiment Start At Field -->
<div class="form-group">
    {!! Form::label('experiment_start_at', 'Experiment Start At:') !!}
    <p>{{ $correlation->experiment_start_at }}</p>
</div>

<!-- Global Variable Relationship Id Field -->
<div class="form-group">
    {!! Form::label('global_variable_relationship_id', 'Global Variable Relationship Id:') !!}
    <p>{{ $correlation->global_variable_relationship_id }}</p>
</div>

<!-- Aggregated At Field -->
<div class="form-group">
    {!! Form::label('aggregated_at', 'Aggregated At:') !!}
    <p>{{ $correlation->aggregated_at }}</p>
</div>

