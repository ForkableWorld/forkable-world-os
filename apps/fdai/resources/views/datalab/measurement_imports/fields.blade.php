<!-- User Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('user_id', 'User Id:') !!}
    {!! Form::number('user_id', null, ['class' => 'form-control']) !!}
</div>

<!-- File Field -->
<div class="form-group col-sm-6">
    {!! Form::label('file', 'File:') !!}
    {!! Form::text('file', null, ['class' => 'form-control']) !!}
</div>

<!-- Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('status', 'Status:') !!}
    {!! Form::text('status', null, ['class' => 'form-control']) !!}
</div>

<!-- Error Message Field -->
<div class="form-group col-sm-12 col-lg-12">
    {!! Form::label('error_message', 'Error Message:') !!}
    {!! Form::textarea('error_message', null, ['class' => 'form-control']) !!}
</div>

<!-- Source Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('source_name', 'Source Name:') !!}
    {!! Form::text('source_name', null, ['class' => 'form-control']) !!}
</div>

<!-- Client Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('client_id', 'Client Id:') !!}
    {!! Form::text('client_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    <a href="{{ route('datalab.measurementImports.index') }}" class="btn btn-default">Cancel</a>
</div>
