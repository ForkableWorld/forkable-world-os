<!-- Connector Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('connector_id', 'Connector Id:') !!}
    {!! Form::number('connector_id', null, ['class' => 'form-control']) !!}
</div>

<!-- User Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('user_id', 'User Id:') !!}
    {!! Form::number('user_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Connection Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('connection_id', 'Connection Id:') !!}
    {!! Form::number('connection_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Connector Import Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('connector_import_id', 'Connector Import Id:') !!}
    {!! Form::number('connector_import_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Method Field -->
<div class="form-group col-sm-6">
    {!! Form::label('method', 'Method:') !!}
    {!! Form::text('method', null, ['class' => 'form-control']) !!}
</div>

<!-- Code Field -->
<div class="form-group col-sm-6">
    {!! Form::label('code', 'Code:') !!}
    {!! Form::number('code', null, ['class' => 'form-control']) !!}
</div>

<!-- Uri Field -->
<div class="form-group col-sm-6">
    {!! Form::label('uri', 'Uri:') !!}
    {!! Form::text('uri', null, ['class' => 'form-control']) !!}
</div>

<!-- Response Body Field -->
<div class="form-group col-sm-12 col-lg-12">
    {!! Form::label('response_body', 'Response Body:') !!}
    {!! Form::textarea('response_body', null, ['class' => 'form-control']) !!}
</div>

<!-- Request Body Field -->
<div class="form-group col-sm-12 col-lg-12">
    {!! Form::label('request_body', 'Request Body:') !!}
    {!! Form::textarea('request_body', null, ['class' => 'form-control']) !!}
</div>

<!-- Request Headers Field -->
<div class="form-group col-sm-12 col-lg-12">
    {!! Form::label('request_headers', 'Request Headers:') !!}
    {!! Form::textarea('request_headers', null, ['class' => 'form-control']) !!}
</div>

<!-- Content Type Field -->
<div class="form-group col-sm-6">
    {!! Form::label('content_type', 'Content Type:') !!}
    {!! Form::text('content_type', null, ['class' => 'form-control']) !!}
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    <a href="{{ route('datalab.connectorRequests.index') }}" class="btn btn-default">Cancel</a>
</div>
