<!-- User Id Field -->
<div class="form-group">
    {!! Form::label('user_id', 'User Id:') !!}
    <p>{{ $measurementImport->user_id }}</p>
</div>

<!-- File Field -->
<div class="form-group">
    {!! Form::label('file', 'File:') !!}
    <p>{{ $measurementImport->file }}</p>
</div>

<!-- Status Field -->
<div class="form-group">
    {!! Form::label('status', 'Status:') !!}
    <p>{{ $measurementImport->status }}</p>
</div>

<!-- Error Message Field -->
<div class="form-group">
    {!! Form::label('error_message', 'Error Message:') !!}
    <p>{{ $measurementImport->error_message }}</p>
</div>

<!-- Source Name Field -->
<div class="form-group">
    {!! Form::label('source_name', 'Source Name:') !!}
    <p>{{ $measurementImport->source_name }}</p>
</div>

<!-- Client Id Field -->
<div class="form-group">
    {!! Form::label('client_id', 'Client Id:') !!}
    <p>{{ $measurementImport->client_id }}</p>
</div>

