<!-- Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('name', 'Name:') !!}
    {!! Form::text('name', null, ['class' => 'form-control']) !!}
</div>

<!-- Filling Value Field -->
<div class="form-group col-sm-6">
    {!! Form::label('filling_value', 'Filling Value:') !!}
    {!! Form::number('filling_value', null, ['class' => 'form-control']) !!}
</div>

<!-- Maximum Allowed Value Field -->
<div class="form-group col-sm-6">
    {!! Form::label('maximum_allowed_value', 'Maximum Allowed Value:') !!}
    {!! Form::number('maximum_allowed_value', null, ['class' => 'form-control']) !!}
</div>

<!-- Minimum Allowed Value Field -->
<div class="form-group col-sm-6">
    {!! Form::label('minimum_allowed_value', 'Minimum Allowed Value:') !!}
    {!! Form::number('minimum_allowed_value', null, ['class' => 'form-control']) !!}
</div>

<!-- Duration Of Action Field -->
<div class="form-group col-sm-6">
    {!! Form::label('duration_of_action', 'Duration Of Action:') !!}
    {!! Form::number('duration_of_action', null, ['class' => 'form-control']) !!}
</div>

<!-- Onset Delay Field -->
<div class="form-group col-sm-6">
    {!! Form::label('onset_delay', 'Onset Delay:') !!}
    {!! Form::number('onset_delay', null, ['class' => 'form-control']) !!}
</div>

<!-- Combination Operation Field -->
<div class="form-group col-sm-6">
    {!! Form::label('combination_operation', 'Combination Operation:') !!}
    {!! Form::text('combination_operation', null, ['class' => 'form-control']) !!}
</div>

<!-- Updated Field -->
<div class="form-group col-sm-6">
    {!! Form::label('updated', 'Updated:') !!}
    {!! Form::number('updated', null, ['class' => 'form-control']) !!}
</div>

<!-- Cause Only Field -->
<div class="form-group col-sm-6">
    {!! Form::label('cause_only', 'Cause Only:') !!}
    <label class="checkbox-inline">
        {!! Form::hidden('cause_only', 0) !!}
        {!! Form::checkbox('cause_only', '1', null) !!}
    </label>
</div>


<!-- Public Field -->
<div class="form-group col-sm-6">
    {!! Form::label('is_public', 'Public:') !!}
    {!! Form::number('is_public', null, ['class' => 'form-control']) !!}
</div>

<!-- Outcome Field -->
<div class="form-group col-sm-6">
    {!! Form::label('outcome', 'Outcome:') !!}
    <label class="checkbox-inline">
        {!! Form::hidden('outcome', 0) !!}
        {!! Form::checkbox('outcome', '1', null) !!}
    </label>
</div>


<!-- Image Url Field -->
<div class="form-group col-sm-12 col-lg-12">
    {!! Form::label('image_url', 'Image Url:') !!}
    {!! Form::textarea('image_url', null, ['class' => 'form-control']) !!}
</div>

<!-- Default Unit Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('default_unit_id', 'Default Unit Id:') !!}
    {!! Form::number('default_unit_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Manual Tracking Field -->
<div class="form-group col-sm-6">
    {!! Form::label('manual_tracking', 'Manual Tracking:') !!}
    <label class="checkbox-inline">
        {!! Form::hidden('manual_tracking', 0) !!}
        {!! Form::checkbox('manual_tracking', '1', null) !!}
    </label>
</div>


<!-- Minimum Allowed Seconds Between Measurements Field -->
<div class="form-group col-sm-6">
    {!! Form::label('minimum_allowed_seconds_between_measurements', 'Minimum Allowed Seconds Between Measurements:') !!}
    {!! Form::number('minimum_allowed_seconds_between_measurements', null, ['class' => 'form-control']) !!}
</div>

<!-- Average Seconds Between Measurements Field -->
<div class="form-group col-sm-6">
    {!! Form::label('average_seconds_between_measurements', 'Average Seconds Between Measurements:') !!}
    {!! Form::number('average_seconds_between_measurements', null, ['class' => 'form-control']) !!}
</div>

<!-- Median Seconds Between Measurements Field -->
<div class="form-group col-sm-6">
    {!! Form::label('median_seconds_between_measurements', 'Median Seconds Between Measurements:') !!}
    {!! Form::number('median_seconds_between_measurements', null, ['class' => 'form-control']) !!}
</div>

<!-- Wp Post Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('wp_post_id', 'Wp Post Id:') !!}
    {!! Form::number('wp_post_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    <a href="{{ route('datalab.variableCategories.index') }}" class="btn btn-default">Cancel</a>
</div>
