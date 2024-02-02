<!-- Client Id Field -->
<div class="form-group">
    {!! Form::label('client_id', 'Client Id:') !!}
    <p>{{ $vote->client_id }}</p>
</div>

<!-- User Id Field -->
<div class="form-group">
    {!! Form::label('user_id', 'User Id:') !!}
    <p>{{ $vote->user_id }}</p>
</div>

<!-- Value Field -->
<div class="form-group">
    {!! Form::label('value', 'Value:') !!}
    <p>{{ $vote->value }}</p>
</div>

<!-- Cause Variable Id Field -->
<div class="form-group">
    {!! Form::label('cause_variable_id', 'Cause Variable Id:') !!}
    <p>{{ $vote->cause_variable_id }}</p>
</div>

<!-- Effect Variable Id Field -->
<div class="form-group">
    {!! Form::label('effect_variable_id', 'Effect Variable Id:') !!}
    <p>{{ $vote->effect_variable_id }}</p>
</div>

