<?php /** @var App\Models\Measurement $measurement */ ?>
<!-- User Id Field -->
<div class="form-group">
    {!! Form::label('user_id', 'User Id:') !!}
    <p>{{ $measurement->user_id }}</p>
</div>

<!-- Client Id Field -->
<div class="form-group">
    {!! Form::label('client_id', 'Client Id:') !!}
    <p>{{ $measurement->client_id }}</p>
</div>

<!-- Connector Id Field -->
<div class="form-group">
    {!! Form::label('connector_id', 'Connector Id:') !!}
    <p>{{ $measurement->connector_id }}</p>
</div>

<!-- Variable Id Field -->
<div class="form-group">
    {!! Form::label('variable_id', 'Variable Id:') !!}
    <p>{{ $measurement->variable_id }}</p>
</div>

<!-- Start Time Field -->
<div class="form-group">
    {!! Form::label('start_time', 'Start Time:') !!}
    <p>{{ $measurement->start_time }}</p>
</div>

<!-- Value Field -->
<div class="form-group">
    {!! Form::label('value', 'Value:') !!}
    <p>{{ $measurement->value }}</p>
</div>

<!-- Unit Id Field -->
<div class="form-group">
    {!! Form::label('unit_id', 'Unit Id:') !!}
    <p>{{ $measurement->unit_id }}</p>
</div>

<!-- Original Value Field -->
<div class="form-group">
    {!! Form::label('original_value', 'Original Value:') !!}
    <p>{{ $measurement->original_value }}</p>
</div>

<!-- Original Unit Id Field -->
<div class="form-group">
    {!! Form::label('original_unit_id', 'Original Unit Id:') !!}
    <p>{{ $measurement->original_unit_id }}</p>
</div>

<!-- Duration Field -->
<div class="form-group">
    {!! Form::label('duration', 'Duration:') !!}
    <p>{{ $measurement->duration }}</p>
</div>

<!-- Note Field -->
<div class="form-group">
    {!! Form::label('note', 'Raw Note:') !!}
    <p>{!! \App\Logging\QMLog::print_r($measurement->note, true) !!}</p>
</div>

<!-- Latitude Field -->
<div class="form-group">
    {!! Form::label('latitude', 'Latitude:') !!}
    <p>{{ $measurement->latitude }}</p>
</div>

<!-- Longitude Field -->
<div class="form-group">
    {!! Form::label('longitude', 'Longitude:') !!}
    <p>{{ $measurement->longitude }}</p>
</div>

<!-- Location Field -->
<div class="form-group">
    {!! Form::label('location', 'Location:') !!}
    <p>{{ $measurement->location }}</p>
</div>

<!-- Error Field -->
<div class="form-group">
    {!! Form::label('error', 'Error:') !!}
    <p>{{ $measurement->error }}</p>
</div>

<!-- Variable Category Id Field -->
<div class="form-group">
    {!! Form::label('variable_category_id', 'Variable Category Id:') !!}
    <p>{{ $measurement->variable_category_id }}</p>
</div>

<!-- Source Name Field -->
<div class="form-group">
    {!! Form::label('source_name', 'Source Name:') !!}
    <p>{{ $measurement->source_name }}</p>
</div>

<!-- User Variable Id Field -->
<div class="form-group">
    {!! Form::label('user_variable_id', 'User Variable Id:') !!}
    <p>{{ $measurement->user_variable_id }}</p>
</div>

<!-- Start At Field -->
<div class="form-group">
    {!! Form::label('start_at', 'Start At:') !!}
    <p>{{ $measurement->start_at }}</p>
</div>

<!-- Connection Id Field -->
<div class="form-group">
    {!! Form::label('connection_id', 'Connection Id:') !!}
    <p>{{ $measurement->connection_id }}</p>
</div>

<!-- Connector Import Id Field -->
<div class="form-group">
    {!! Form::label('connector_import_id', 'Connector Import Id:') !!}
    <p>{{ $measurement->connector_import_id }}</p>
</div>

