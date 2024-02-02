<!-- Tagged Variable Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('tagged_variable_id', 'Tagged Variable Id:') !!}
    {!! Form::number('tagged_variable_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Tag Variable Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('tag_variable_id', 'Tag Variable Id:') !!}
    {!! Form::number('tag_variable_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Number Of Data Points Field -->
<div class="form-group col-sm-6">
    {!! Form::label('number_of_data_points', 'Number Of Data Points:') !!}
    {!! Form::number('number_of_data_points', null, ['class' => 'form-control']) !!}
</div>

<!-- Standard Error Field -->
<div class="form-group col-sm-6">
    {!! Form::label('standard_error', 'Standard Error:') !!}
    {!! Form::number('standard_error', null, ['class' => 'form-control']) !!}
</div>

<!-- Tag Variable Unit Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('tag_variable_unit_id', 'Tag Variable Unit Id:') !!}
    {!! Form::number('tag_variable_unit_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Tagged Variable Unit Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('tagged_variable_unit_id', 'Tagged Variable Unit Id:') !!}
    {!! Form::number('tagged_variable_unit_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Conversion Factor Field -->
<div class="form-group col-sm-6">
    {!! Form::label('conversion_factor', 'Conversion Factor:') !!}
    {!! Form::number('conversion_factor', null, ['class' => 'form-control']) !!}
</div>

<!-- Client Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('client_id', 'Client Id:') !!}
    {!! Form::text('client_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    <a href="{{ route('datalab.commonTags.index') }}" class="btn btn-default">Cancel</a>
</div>
