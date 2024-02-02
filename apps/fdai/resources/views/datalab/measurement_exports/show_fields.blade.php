<!-- User Id Field -->
<div class="form-group">
    {!! Form::label('user_id', 'User Id:') !!}
    <p>{{ $measurementExport->user_id }}</p>
</div>

<!-- Client Id Field -->
<div class="form-group">
    {!! Form::label('client_id', 'Client Id:') !!}
    <p>{{ $measurementExport->client_id }}</p>
</div>

<!-- Status Field -->
<div class="form-group">
    {!! Form::label('status', 'Status:') !!}
    <p>{{ $measurementExport->status }}</p>
</div>

<!-- Type Field -->
<div class="form-group">
    {!! Form::label('type', 'Type:') !!}
    <p>{{ $measurementExport->type }}</p>
</div>

<!-- Output Type Field -->
<div class="form-group">
    {!! Form::label('output_type', 'Output Type:') !!}
    <p>{{ $measurementExport->output_type }}</p>
</div>

<!-- Error Message Field -->
<div class="form-group">
    {!! Form::label('error_message', 'Error Message:') !!}
    <p>{{ $measurementExport->error_message }}</p>
</div>

