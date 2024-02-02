<!-- Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('name', 'Name:') !!}
    {!! Form::text('name', null, ['class' => 'form-control']) !!}
</div>

<!-- Abbreviated Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('abbreviated_name', 'Abbreviated Name:') !!}
    {!! Form::text('abbreviated_name', null, ['class' => 'form-control']) !!}
</div>

<!-- Category Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('category_id', 'Category Id:') !!}
    <label class="checkbox-inline">
        {!! Form::hidden('category_id', 0) !!}
        {!! Form::checkbox('category_id', '1', null) !!}
    </label>
</div>


<!-- Minimum Value Field -->
<div class="form-group col-sm-6">
    {!! Form::label('minimum_value', 'Minimum Value:') !!}
    {!! Form::number('minimum_value', null, ['class' => 'form-control']) !!}
</div>

<!-- Maximum Value Field -->
<div class="form-group col-sm-6">
    {!! Form::label('maximum_value', 'Maximum Value:') !!}
    {!! Form::number('maximum_value', null, ['class' => 'form-control']) !!}
</div>

<!-- Multiply Field -->
<div class="form-group col-sm-6">
    {!! Form::label('multiply', 'Multiply:') !!}
    {!! Form::number('multiply', null, ['class' => 'form-control']) !!}
</div>

<!-- Add Field -->
<div class="form-group col-sm-6">
    {!! Form::label('add', 'Add:') !!}
    {!! Form::number('add', null, ['class' => 'form-control']) !!}
</div>

<!-- Filling Type Field -->
<div class="form-group col-sm-6">
    {!! Form::label('filling_type', 'Filling Type:') !!}
    {!! Form::text('filling_type', null, ['class' => 'form-control']) !!}
</div>

<!-- Number Of Outcome Population Studies Field -->
<div class="form-group col-sm-6">
    {!! Form::label('number_of_outcome_population_studies', 'Number Of Outcome Population Studies:') !!}
    {!! Form::number('number_of_outcome_population_studies', null, ['class' => 'form-control']) !!}
</div>

<!-- Number Of Common Tags Where Tag Variable Unit Field -->
<div class="form-group col-sm-6">
    {!! Form::label('number_of_common_tags_where_tag_variable_unit', 'Number Of Common Tags Where Tag Variable Unit:') !!}
    {!! Form::number('number_of_common_tags_where_tag_variable_unit', null, ['class' => 'form-control']) !!}
</div>

<!-- Number Of Common Tags Where Tagged Variable Unit Field -->
<div class="form-group col-sm-6">
    {!! Form::label('number_of_common_tags_where_tagged_variable_unit', 'Number Of Common Tags Where Tagged Variable Unit:') !!}
    {!! Form::number('number_of_common_tags_where_tagged_variable_unit', null, ['class' => 'form-control']) !!}
</div>

<!-- Number Of Outcome Case Studies Field -->
<div class="form-group col-sm-6">
    {!! Form::label('number_of_outcome_case_studies', 'Number Of Outcome Case Studies:') !!}
    {!! Form::number('number_of_outcome_case_studies', null, ['class' => 'form-control']) !!}
</div>

<!-- Number Of Measurements Field -->
<div class="form-group col-sm-6">
    {!! Form::label('number_of_measurements', 'Number Of Measurements:') !!}
    {!! Form::number('number_of_measurements', null, ['class' => 'form-control']) !!}
</div>

<!-- Number Of User Variables Where Default Unit Field -->
<div class="form-group col-sm-6">
    {!! Form::label('number_of_user_variables_where_default_unit', 'Number Of User Variables Where Default Unit:') !!}
    {!! Form::number('number_of_user_variables_where_default_unit', null, ['class' => 'form-control']) !!}
</div>

<!-- Number Of Variable Categories Where Default Unit Field -->
<div class="form-group col-sm-6">
    {!! Form::label('number_of_variable_categories_where_default_unit', 'Number Of Variable Categories Where Default Unit:') !!}
    {!! Form::number('number_of_variable_categories_where_default_unit', null, ['class' => 'form-control']) !!}
</div>

<!-- Number Of Variables Where Default Unit Field -->
<div class="form-group col-sm-6">
    {!! Form::label('number_of_variables_where_default_unit', 'Number Of Variables Where Default Unit:') !!}
    {!! Form::number('number_of_variables_where_default_unit', null, ['class' => 'form-control']) !!}
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    <a href="{{ route('datalab.units.index') }}" class="btn btn-default">Cancel</a>
</div>
