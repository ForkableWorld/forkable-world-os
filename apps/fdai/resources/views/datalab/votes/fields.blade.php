<!-- Client Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('client_id', 'Client Id:') !!}
    {!! Form::text('client_id', null, ['class' => 'form-control']) !!}
</div>

<!-- User Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('user_id', 'User Id:') !!}
    {!! Form::number('user_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Value Field -->
<div class="form-group col-sm-6">
    {!! Form::label('value', 'Value:') !!}
    {!! Form::number('value', null, ['class' => 'form-control']) !!}
</div>

<!-- Cause Variable Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('cause_variable_id', 'Cause Variable Id:') !!}
    {!! Form::number('cause_variable_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Effect Variable Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('effect_variable_id', 'Effect Variable Id:') !!}
    {!! Form::number('effect_variable_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    <a href="{{ route('datalab.votes.index') }}" class="btn btn-default">Cancel</a>
</div>
