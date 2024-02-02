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

<!-- Conversion Factor Field -->
<div class="form-group col-sm-6">
    {!! Form::label('conversion_factor', 'Conversion Factor:') !!}
    {!! Form::number('conversion_factor', null, ['class' => 'form-control']) !!}
</div>

<!-- User Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('user_id', 'User Id:') !!}
    {!! Form::number('user_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Client Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('client_id', 'Client Id:') !!}
    {!! Form::text('client_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    <a href="{{ route('datalab.userTags.index') }}" class="btn btn-default">Cancel</a>
</div>
