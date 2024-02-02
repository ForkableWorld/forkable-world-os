<!-- Name Field -->
<div class="form-group">
    {!! Form::label('name', 'Name:') !!}
    <p>{{ $variableCategory->name }}</p>
</div>

<!-- Filling Value Field -->
<div class="form-group">
    {!! Form::label('filling_value', 'Filling Value:') !!}
    <p>{{ $variableCategory->filling_value }}</p>
</div>

<!-- Maximum Allowed Value Field -->
<div class="form-group">
    {!! Form::label('maximum_allowed_value', 'Maximum Allowed Value:') !!}
    <p>{{ $variableCategory->maximum_allowed_value }}</p>
</div>

<!-- Minimum Allowed Value Field -->
<div class="form-group">
    {!! Form::label('minimum_allowed_value', 'Minimum Allowed Value:') !!}
    <p>{{ $variableCategory->minimum_allowed_value }}</p>
</div>

<!-- Duration Of Action Field -->
<div class="form-group">
    {!! Form::label('duration_of_action', 'Duration Of Action:') !!}
    <p>{{ $variableCategory->duration_of_action }}</p>
</div>

<!-- Onset Delay Field -->
<div class="form-group">
    {!! Form::label('onset_delay', 'Onset Delay:') !!}
    <p>{{ $variableCategory->onset_delay }}</p>
</div>

<!-- Combination Operation Field -->
<div class="form-group">
    {!! Form::label('combination_operation', 'Combination Operation:') !!}
    <p>{{ $variableCategory->combination_operation }}</p>
</div>

<!-- Updated Field -->
<div class="form-group">
    {!! Form::label('updated', 'Updated:') !!}
    <p>{{ $variableCategory->updated }}</p>
</div>

<!-- Cause Only Field -->
<div class="form-group">
    {!! Form::label('cause_only', 'Cause Only:') !!}
    <p>{{ $variableCategory->cause_only }}</p>
</div>

<!-- Public Field -->
<div class="form-group">
    {!! Form::label('is_public', 'Public:') !!}
    <p>{{ $variableCategory->is_public }}</p>
</div>

<!-- Outcome Field -->
<div class="form-group">
    {!! Form::label('outcome', 'Outcome:') !!}
    <p>{{ $variableCategory->outcome }}</p>
</div>

<!-- Image Url Field -->
<div class="form-group">
    {!! Form::label('image_url', 'Image Url:') !!}
    <p>{{ $variableCategory->image_url }}</p>
</div>

<!-- Default Unit Id Field -->
<div class="form-group">
    {!! Form::label('default_unit_id', 'Default Unit Id:') !!}
    <p>{{ $variableCategory->default_unit_id }}</p>
</div>

<!-- Manual Tracking Field -->
<div class="form-group">
    {!! Form::label('manual_tracking', 'Manual Tracking:') !!}
    <p>{{ $variableCategory->manual_tracking }}</p>
</div>

<!-- Minimum Allowed Seconds Between Measurements Field -->
<div class="form-group">
    {!! Form::label('minimum_allowed_seconds_between_measurements', 'Minimum Allowed Seconds Between Measurements:') !!}
    <p>{{ $variableCategory->minimum_allowed_seconds_between_measurements }}</p>
</div>

<!-- Average Seconds Between Measurements Field -->
<div class="form-group">
    {!! Form::label('average_seconds_between_measurements', 'Average Seconds Between Measurements:') !!}
    <p>{{ $variableCategory->average_seconds_between_measurements }}</p>
</div>

<!-- Median Seconds Between Measurements Field -->
<div class="form-group">
    {!! Form::label('median_seconds_between_measurements', 'Median Seconds Between Measurements:') !!}
    <p>{{ $variableCategory->median_seconds_between_measurements }}</p>
</div>

<!-- Wp Post Id Field -->
<div class="form-group">
    {!! Form::label('wp_post_id', 'Wp Post Id:') !!}
    <p>{{ $variableCategory->wp_post_id }}</p>
</div>

