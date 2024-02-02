<!-- Client Id Field -->
<div class="form-group">
    {!! Form::label('client_id', 'Client Id:') !!}
    <p>{{ $connectorImport->client_id }}</p>
</div>

<!-- Connection Id Field -->
<div class="form-group">
    {!! Form::label('connection_id', 'Connection Id:') !!}
    <p>{{ $connectorImport->connection_id }}</p>
</div>

<!-- Connector Id Field -->
<div class="form-group">
    {!! Form::label('connector_id', 'Connector Id:') !!}
    <p>{{ $connectorImport->connector_id }}</p>
</div>

<!-- Earliest Measurement At Field -->
<div class="form-group">
    {!! Form::label('earliest_measurement_at', 'Earliest Measurement At:') !!}
    <p>{{ $connectorImport->earliest_measurement_at }}</p>
</div>

<!-- Import Ended At Field -->
<div class="form-group">
    {!! Form::label('import_ended_at', 'Import Ended At:') !!}
    <p>{{ $connectorImport->import_ended_at }}</p>
</div>

<!-- Import Started At Field -->
<div class="form-group">
    {!! Form::label('import_started_at', 'Import Started At:') !!}
    <p>{{ $connectorImport->import_started_at }}</p>
</div>

<!-- Internal Error Message Field -->
<div class="form-group">
    {!! Form::label('internal_error_message', 'Internal Error Message:') !!}
    <p>{{ $connectorImport->internal_error_message }}</p>
</div>

<!-- Latest Measurement At Field -->
<div class="form-group">
    {!! Form::label('latest_measurement_at', 'Latest Measurement At:') !!}
    <p>{{ $connectorImport->latest_measurement_at }}</p>
</div>

<!-- Number Of Measurements Field -->
<div class="form-group">
    {!! Form::label('number_of_measurements', 'Number Of Measurements:') !!}
    <p>{{ $connectorImport->number_of_measurements }}</p>
</div>

<!-- Reason For Import Field -->
<div class="form-group">
    {!! Form::label('reason_for_import', 'Reason For Import:') !!}
    <p>{{ $connectorImport->reason_for_import }}</p>
</div>

<!-- Success Field -->
<div class="form-group">
    {!! Form::label('success', 'Success:') !!}
    <p>{{ $connectorImport->success }}</p>
</div>

<!-- User Error Message Field -->
<div class="form-group">
    {!! Form::label('user_error_message', 'User Error Message:') !!}
    <p>{{ $connectorImport->user_error_message }}</p>
</div>

<!-- User Id Field -->
<div class="form-group">
    {!! Form::label('user_id', 'User Id:') !!}
    <p>{{ $connectorImport->user_id }}</p>
</div>

<!-- Additional Meta Data Field -->
<div class="form-group">
    {!! Form::label('additional_meta_data', 'Additional Meta Data:') !!}
    <p>{{ $connectorImport->additional_meta_data }}</p>
</div>

