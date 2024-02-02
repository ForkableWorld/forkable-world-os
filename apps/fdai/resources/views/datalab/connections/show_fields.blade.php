<!-- Client Id Field -->
<div class="form-group">
    {!! Form::label('client_id', 'Client Id:') !!}
    <p>{{ $connection->client_id }}</p>
</div>

<!-- User Id Field -->
<div class="form-group">
    {!! Form::label('user_id', 'User Id:') !!}
    <p>{{ $connection->user_id }}</p>
</div>

<!-- Connector Id Field -->
<div class="form-group">
    {!! Form::label('connector_id', 'Connector Id:') !!}
    <p>{{ $connection->connector_id }}</p>
</div>

<!-- Connect Status Field -->
<div class="form-group">
    {!! Form::label('connect_status', 'Connect Status:') !!}
    <p>{{ $connection->connect_status }}</p>
</div>

<!-- Connect Error Field -->
<div class="form-group">
    {!! Form::label('connect_error', 'Connect Error:') !!}
    <p>{{ $connection->connect_error }}</p>
</div>

<!-- Update Requested At Field -->
<div class="form-group">
    {!! Form::label('update_requested_at', 'Update Requested At:') !!}
    <p>{{ $connection->update_requested_at }}</p>
</div>

<!-- Update Status Field -->
<div class="form-group">
    {!! Form::label('update_status', 'Update Status:') !!}
    <p>{{ $connection->update_status }}</p>
</div>

<!-- Update Error Field -->
<div class="form-group">
    {!! Form::label('update_error', 'Update Error:') !!}
    <p>{{ $connection->update_error }}</p>
</div>

<!-- Last Successful Updated At Field -->
<div class="form-group">
    {!! Form::label('last_successful_updated_at', 'Last Successful Updated At:') !!}
    <p>{{ $connection->last_successful_updated_at }}</p>
</div>

<!-- Total Measurements In Last Update Field -->
<div class="form-group">
    {!! Form::label('total_measurements_in_last_update', 'Total Measurements In Last Update:') !!}
    <p>{{ $connection->total_measurements_in_last_update }}</p>
</div>

<!-- User Message Field -->
<div class="form-group">
    {!! Form::label('user_message', 'User Message:') !!}
    <p>{{ $connection->user_message }}</p>
</div>

<!-- Latest Measurement At Field -->
<div class="form-group">
    {!! Form::label('latest_measurement_at', 'Latest Measurement At:') !!}
    <p>{{ $connection->latest_measurement_at }}</p>
</div>

<!-- Import Started At Field -->
<div class="form-group">
    {!! Form::label('import_started_at', 'Import Started At:') !!}
    <p>{{ $connection->import_started_at }}</p>
</div>

<!-- Import Ended At Field -->
<div class="form-group">
    {!! Form::label('import_ended_at', 'Import Ended At:') !!}
    <p>{{ $connection->import_ended_at }}</p>
</div>

<!-- Reason For Import Field -->
<div class="form-group">
    {!! Form::label('reason_for_import', 'Reason For Import:') !!}
    <p>{{ $connection->reason_for_import }}</p>
</div>

<!-- User Error Message Field -->
<div class="form-group">
    {!! Form::label('user_error_message', 'User Error Message:') !!}
    <p>{{ $connection->user_error_message }}</p>
</div>

<!-- Internal Error Message Field -->
<div class="form-group">
    {!! Form::label('internal_error_message', 'Internal Error Message:') !!}
    <p>{{ $connection->internal_error_message }}</p>
</div>

<!-- Wp Post Id Field -->
<div class="form-group">
    {!! Form::label('wp_post_id', 'Wp Post Id:') !!}
    <p>{{ $connection->wp_post_id }}</p>
</div>

