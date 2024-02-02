<?php /** @var App\Models\ConnectorRequest $connectorRequest */ ?>


<!-- Connector Id Field -->
<div class="form-group">
    {!! Form::label('connector_id', 'Connector Id') !!}
    <p>{{ $connectorRequest->connector_id }}</p>
</div>

<!-- User Id Field -->
<div class="form-group">
    {!! Form::label('user_id', 'User Id') !!}
    <p>{{ $connectorRequest->user_id }}</p>
</div>

<!-- Connection Id Field -->
<div class="form-group">
    {!! Form::label('connection_id', 'Connection Id') !!}
    <p>{{ $connectorRequest->conn }}</p>
</div>

<!-- Connector Import Id Field -->
<div class="form-group">
    {!! Form::label('connector_import_id', 'Connector Import Id') !!}
    <p>{{ $connectorRequest->connector_import_id }}</p>
</div>

<!-- Method Field -->
<div class="form-group">
    {!! Form::label('method', 'Method') !!}
    <p>{{ $connectorRequest->method }}</p>
</div>

<!-- Code Field -->
<div class="form-group">
    {!! Form::label('code', 'Code') !!}
    <p>{{ $connectorRequest->code }}</p>
</div>

<!-- Uri Field -->
<div class="form-group">
    {!! Form::label('uri', 'Uri') !!}
    <p>{{ $connectorRequest->uri }}</p>
</div>

<!-- Response Body Field -->
<div class="form-group">
    {!! Form::label('response_body', 'Response Body') !!}
    <pre>{{ \App\Logging\QMLog::print_r($connectorRequest->response_body, true) }}</pre>
</div>

<!-- Request Body Field -->
<div class="form-group">
    {!! Form::label('request_body', 'Request Body') !!}
    <pre>{{ \App\Logging\QMLog::print_r($connectorRequest->request_body, true) }}</pre>
</div>

<!-- Request Headers Field -->
<div class="form-group">
    {!! Form::label('request_headers', 'Request Headers') !!}
    <pre>{{ \App\Logging\QMLog::print_r($connectorRequest->request_headers, true) }}</pre>
</div>

<!-- Content Type Field -->
<div class="form-group">
    {!! Form::label('content_type', 'Content Type') !!}
    <p>{{ $connectorRequest->content_type }}</p>
</div>

