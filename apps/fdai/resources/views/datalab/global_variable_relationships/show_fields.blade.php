<!-- Forward Pearson UserVariableRelationship Coefficient Field -->
<?php /** @var \App\Models\GlobalVariableRelationship $aggregateCorrelation */ ?>
<div class="form-group">
    {!! Form::label('forward_pearson_correlation_coefficient', 'Forward Pearson User Variable Relationship Coefficient:') !!}
    <p>{{ $aggregateCorrelation->forward_pearson_correlation_coefficient }}</p>
</div>

<!-- Onset Delay Field -->
<div class="form-group">
    {!! Form::label('onset_delay', 'Onset Delay:') !!}
    <p>{{ $aggregateCorrelation->onset_delay }}</p>
</div>

<!-- Duration Of Action Field -->
<div class="form-group">
    {!! Form::label('duration_of_action', 'Duration Of Action:') !!}
    <p>{{ $aggregateCorrelation->duration_of_action }}</p>
</div>

<!-- Number Of Pairs Field -->
<div class="form-group">
    {!! Form::label('number_of_pairs', 'Number Of Pairs:') !!}
    <p>{{ $aggregateCorrelation->number_of_pairs }}</p>
</div>

<!-- Value Predicting High Outcome Field -->
<div class="form-group">
    {!! Form::label('value_predicting_high_outcome', 'Value Predicting High Outcome:') !!}
    <p>{{ $aggregateCorrelation->value_predicting_high_outcome }}</p>
</div>

<!-- Value Predicting Low Outcome Field -->
<div class="form-group">
    {!! Form::label('value_predicting_low_outcome', 'Value Predicting Low Outcome:') !!}
    <p>{{ $aggregateCorrelation->value_predicting_low_outcome }}</p>
</div>

<!-- Optimal Pearson Product Field -->
<div class="form-group">
    {!! Form::label('optimal_pearson_product', 'Optimal Pearson Product:') !!}
    <p>{{ $aggregateCorrelation->optimal_pearson_product }}</p>
</div>

<!-- Average Vote Field -->
<div class="form-group">
    {!! Form::label('average_vote', 'Average Vote:') !!}
    <p>{{ $aggregateCorrelation->average_vote }}</p>
</div>

<!-- Number Of Users Field -->
<div class="form-group">
    {!! Form::label('number_of_users', 'Number Of Users:') !!}
    <p>{{ $aggregateCorrelation->number_of_users }}</p>
</div>

<!-- Number Of VariableRelationships Field -->
<div class="form-group">
    {!! Form::label('number_of_correlations', 'Number Of VariableRelationships:') !!}
    <p>{{ $aggregateCorrelation->number_of_correlations }}</p>
</div>

<!-- Statistical Significance Field -->
<div class="form-group">
    {!! Form::label('statistical_significance', 'Statistical Significance:') !!}
    <p>{{ $aggregateCorrelation->statistical_significance }}</p>
</div>

<!-- Cause Unit Id Field -->
<div class="form-group">
    {!! Form::label('cause_unit_id', 'Cause Unit Id:') !!}
    <p>{{ $aggregateCorrelation->cause_unit_id }}</p>
</div>

<!-- Cause Changes Field -->
<div class="form-group">
    {!! Form::label('cause_changes', 'Cause Changes:') !!}
    <p>{{ $aggregateCorrelation->cause_changes }}</p>
</div>

<!-- Effect Changes Field -->
<div class="form-group">
    {!! Form::label('effect_changes', 'Effect Changes:') !!}
    <p>{{ $aggregateCorrelation->effect_changes }}</p>
</div>

<!-- Aggregate Qm Score Field -->
<div class="form-group">
    {!! Form::label('aggregate_qm_score', 'Aggregate Qm Score:') !!}
    <p>{{ $aggregateCorrelation->aggregate_qm_score }}</p>
</div>

<!-- Status Field -->
<div class="form-group">
    {!! Form::label('status', 'Status:') !!}
    <p>{{ $aggregateCorrelation->status }}</p>
</div>

<!-- Reverse Pearson UserVariableRelationship Coefficient Field -->
<div class="form-group">
    {!! Form::label('reverse_pearson_correlation_coefficient', 'Reverse Pearson User Variable Relationship Coefficient:') !!}
    <p>{{ $aggregateCorrelation->reverse_pearson_correlation_coefficient }}</p>
</div>

<!-- Predictive Pearson UserVariableRelationship Coefficient Field -->
<div class="form-group">
    {!! Form::label('predictive_pearson_correlation_coefficient', 'Predictive Pearson User Variable Relationship Coefficient:') !!}
    <p>{{ $aggregateCorrelation->predictive_pearson_correlation_coefficient }}</p>
</div>

<!-- Data Source Name Field -->
<div class="form-group">
    {!! Form::label('data_source_name', 'Data Source Name:') !!}
    <p>{{ $aggregateCorrelation->data_source_name }}</p>
</div>

<!-- Predicts High Effect Change Field -->
<div class="form-group">
    {!! Form::label('predicts_high_effect_change', 'Predicts High Effect Change:') !!}
    <p>{{ $aggregateCorrelation->predicts_high_effect_change }}</p>
</div>

<!-- Predicts Low Effect Change Field -->
<div class="form-group">
    {!! Form::label('predicts_low_effect_change', 'Predicts Low Effect Change:') !!}
    <p>{{ $aggregateCorrelation->predicts_low_effect_change }}</p>
</div>

<!-- P Value Field -->
<div class="form-group">
    {!! Form::label('p_value', 'P Value:') !!}
    <p>{{ $aggregateCorrelation->p_value }}</p>
</div>

<!-- T Value Field -->
<div class="form-group">
    {!! Form::label('t_value', 'T Value:') !!}
    <p>{{ $aggregateCorrelation->t_value }}</p>
</div>

<!-- Critical T Value Field -->
<div class="form-group">
    {!! Form::label('critical_t_value', 'Critical T Value:') !!}
    <p>{{ $aggregateCorrelation->critical_t_value }}</p>
</div>

<!-- Confidence Interval Field -->
<div class="form-group">
    {!! Form::label('confidence_interval', 'Confidence Interval:') !!}
    <p>{{ $aggregateCorrelation->confidence_interval }}</p>
</div>

<!-- Average Effect Field -->
<div class="form-group">
    {!! Form::label('average_effect', 'Average Effect:') !!}
    <p>{{ $aggregateCorrelation->average_effect }}</p>
</div>

<!-- Average Effect Following High Cause Field -->
<div class="form-group">
    {!! Form::label('average_effect_following_high_cause', 'Average Effect Following High Cause:') !!}
    <p>{{ $aggregateCorrelation->average_effect_following_high_cause }}</p>
</div>

<!-- Average Effect Following Low Cause Field -->
<div class="form-group">
    {!! Form::label('average_effect_following_low_cause', 'Average Effect Following Low Cause:') !!}
    <p>{{ $aggregateCorrelation->average_effect_following_low_cause }}</p>
</div>

<!-- Average Daily Low Cause Field -->
<div class="form-group">
    {!! Form::label('average_daily_low_cause', 'Average Daily Low Cause:') !!}
    <p>{{ $aggregateCorrelation->average_daily_low_cause }}</p>
</div>

<!-- Average Daily High Cause Field -->
<div class="form-group">
    {!! Form::label('average_daily_high_cause', 'Average Daily High Cause:') !!}
    <p>{{ $aggregateCorrelation->average_daily_high_cause }}</p>
</div>

<!-- Population Trait Pearson UserVariableRelationship Coefficient Field -->
<div class="form-group">
    {!! Form::label('population_trait_pearson_correlation_coefficient', 'Population Trait Pearson User Variable Relationship Coefficient:') !!}
    <p>{{ $aggregateCorrelation->population_trait_pearson_correlation_coefficient }}</p>
</div>

<!-- Grouped Cause Value Closest To Value Predicting Low Outcome Field -->
<div class="form-group">
    {!! Form::label('grouped_cause_value_closest_to_value_predicting_low_outcome', 'Grouped Cause Value Closest To Value Predicting Low Outcome:') !!}
    <p>{{ $aggregateCorrelation->grouped_cause_value_closest_to_value_predicting_low_outcome }}</p>
</div>

<!-- Grouped Cause Value Closest To Value Predicting High Outcome Field -->
<div class="form-group">
    {!! Form::label('grouped_cause_value_closest_to_value_predicting_high_outcome', 'Grouped Cause Value Closest To Value Predicting High Outcome:') !!}
    <p>{{ $aggregateCorrelation->grouped_cause_value_closest_to_value_predicting_high_outcome }}</p>
</div>

<!-- Client Id Field -->
<div class="form-group">
    {!! Form::label('client_id', 'Client Id:') !!}
    <p>{{ $aggregateCorrelation->client_id }}</p>
</div>

<!-- Published At Field -->
<div class="form-group">
    {!! Form::label('published_at', 'Published At:') !!}
    <p>{{ $aggregateCorrelation->published_at }}</p>
</div>

<!-- Wp Post Id Field -->
<div class="form-group">
    {!! Form::label('wp_post_id', 'Wp Post Id:') !!}
    <p>{{ $aggregateCorrelation->wp_post_id }}</p>
</div>

<!-- Cause Variable Category Id Field -->
<div class="form-group">
    {!! Form::label('cause_variable_category_id', 'Cause Variable Category Id:') !!}
    <p>{{ $aggregateCorrelation->cause_variable_category_id }}</p>
</div>

<!-- Effect Variable Category Id Field -->
<div class="form-group">
    {!! Form::label('effect_variable_category_id', 'Effect Variable Category Id:') !!}
    <p>{{ $aggregateCorrelation->effect_variable_category_id }}</p>
</div>

<!-- Interesting Variable Category Pair Field -->
<div class="form-group">
    {!! Form::label('interesting_variable_category_pair', 'Interesting Variable Category Pair:') !!}
    <p>{{ $aggregateCorrelation->interesting_variable_category_pair }}</p>
</div>

<!-- Newest Data At Field -->
<div class="form-group">
    {!! Form::label('newest_data_at', 'Newest Data At:') !!}
    <p>{{ $aggregateCorrelation->newest_data_at }}</p>
</div>

<!-- Analysis Requested At Field -->
<div class="form-group">
    {!! Form::label('analysis_requested_at', 'Analysis Requested At:') !!}
    <p>{{ $aggregateCorrelation->analysis_requested_at }}</p>
</div>

<!-- Reason For Analysis Field -->
<div class="form-group">
    {!! Form::label('reason_for_analysis', 'Reason For Analysis:') !!}
    <p>{{ $aggregateCorrelation->reason_for_analysis }}</p>
</div>

<!-- Analysis Started At Field -->
<div class="form-group">
    {!! Form::label('analysis_started_at', 'Analysis Started At:') !!}
    <p>{{ $aggregateCorrelation->analysis_started_at }}</p>
</div>

<!-- Analysis Ended At Field -->
<div class="form-group">
    {!! Form::label('analysis_ended_at', 'Analysis Ended At:') !!}
    <p>{{ $aggregateCorrelation->analysis_ended_at }}</p>
</div>

<!-- User Error Message Field -->
<div class="form-group">
    {!! Form::label('user_error_message', 'User Error Message:') !!}
    <p>{{ $aggregateCorrelation->user_error_message }}</p>
</div>

<!-- Internal Error Message Field -->
<div class="form-group">
    {!! Form::label('internal_error_message', 'Internal Error Message:') !!}
    <p>{{ $aggregateCorrelation->internal_error_message }}</p>
</div>

<!-- Cause Variable Id Field -->
<div class="form-group">
    {!! Form::label('cause_variable_id', 'Cause Variable Id:') !!}
    <p>{{ $aggregateCorrelation->cause_variable_id }}</p>
</div>

<!-- Effect Variable Id Field -->
<div class="form-group">
    {!! Form::label('effect_variable_id', 'Effect Variable Id:') !!}
    <p>{{ $aggregateCorrelation->effect_variable_id }}</p>
</div>

<!-- Cause Baseline Average Per Day Field -->
<div class="form-group">
    {!! Form::label('cause_baseline_average_per_day', 'Cause Baseline Average Per Day:') !!}
    <p>{{ $aggregateCorrelation->cause_baseline_average_per_day }}</p>
</div>

<!-- Cause Baseline Average Per Duration Of Action Field -->
<div class="form-group">
    {!! Form::label('cause_baseline_average_per_duration_of_action', 'Cause Baseline Average Per Duration Of Action:') !!}
    <p>{{ $aggregateCorrelation->cause_baseline_average_per_duration_of_action }}</p>
</div>

<!-- Cause Treatment Average Per Day Field -->
<div class="form-group">
    {!! Form::label('cause_treatment_average_per_day', 'Cause Treatment Average Per Day:') !!}
    <p>{{ $aggregateCorrelation->cause_treatment_average_per_day }}</p>
</div>

<!-- Cause Treatment Average Per Duration Of Action Field -->
<div class="form-group">
    {!! Form::label('cause_treatment_average_per_duration_of_action', 'Cause Treatment Average Per Duration Of Action:') !!}
    <p>{{ $aggregateCorrelation->cause_treatment_average_per_duration_of_action }}</p>
</div>

<!-- Effect Baseline Average Field -->
<div class="form-group">
    {!! Form::label('effect_baseline_average', 'Effect Baseline Average:') !!}
    <p>{{ $aggregateCorrelation->effect_baseline_average }}</p>
</div>

<!-- Effect Baseline Relative Standard Deviation Field -->
<div class="form-group">
    {!! Form::label('effect_baseline_relative_standard_deviation', 'Effect Baseline Relative Standard Deviation:') !!}
    <p>{{ $aggregateCorrelation->effect_baseline_relative_standard_deviation }}</p>
</div>

<!-- Effect Baseline Standard Deviation Field -->
<div class="form-group">
    {!! Form::label('effect_baseline_standard_deviation', 'Effect Baseline Standard Deviation:') !!}
    <p>{{ $aggregateCorrelation->effect_baseline_standard_deviation }}</p>
</div>

<!-- Effect Follow Up Average Field -->
<div class="form-group">
    {!! Form::label('effect_follow_up_average', 'Effect Follow Up Average:') !!}
    <p>{{ $aggregateCorrelation->effect_follow_up_average }}</p>
</div>

<!-- Effect Follow Up Percent Change From Baseline Field -->
<div class="form-group">
    {!! Form::label('effect_follow_up_percent_change_from_baseline', 'Effect Follow Up Percent Change From Baseline:') !!}
    <p>{{ $aggregateCorrelation->effect_follow_up_percent_change_from_baseline }}</p>
</div>

<!-- Z Score Field -->
<div class="form-group">
    {!! Form::label('z_score', 'Z Score:') !!}
    <p>{{ $aggregateCorrelation->z_score }}</p>
</div>

<!-- Charts Field -->
<div class="form-group">
    {!! Form::label('charts', 'Charts:') !!}
    {!! $aggregateCorrelation->getChartGroup()->getChartHtmlWithEmbeddedImages() !!}
</div>

