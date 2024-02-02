<!-- Client Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('client_id', 'Client Id:') !!}
    {!! Form::text('client_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Connection Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('connection_id', 'Connection Id:') !!}
    {!! Form::number('connection_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Connector Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('connector_id', 'Connector Id:') !!}
    {!! Form::number('connector_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Earliest Measurement At Field -->
<div class="form-group col-sm-6">
    {!! Form::label('earliest_measurement_at', 'Earliest Measurement At:') !!}
    {!! Form::date('earliest_measurement_at', null, ['class' => 'form-control','id'=>'earliest_measurement_at']) !!}
</div>

@push('scripts')
    <script type="text/javascript">
        $('#earliest_measurement_at').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss',
            useCurrent: false
        })
    </script>
@endpush

<!-- Import Ended At Field -->
<div class="form-group col-sm-6">
    {!! Form::label('import_ended_at', 'Import Ended At:') !!}
    {!! Form::date('import_ended_at', null, ['class' => 'form-control','id'=>'import_ended_at']) !!}
</div>

@push('scripts')
    <script type="text/javascript">
        $('#import_ended_at').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss',
            useCurrent: false
        })
    </script>
@endpush

<!-- Import Started At Field -->
<div class="form-group col-sm-6">
    {!! Form::label('import_started_at', 'Import Started At:') !!}
    {!! Form::date('import_started_at', null, ['class' => 'form-control','id'=>'import_started_at']) !!}
</div>

@push('scripts')
    <script type="text/javascript">
        $('#import_started_at').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss',
            useCurrent: false
        })
    </script>
@endpush

<!-- Internal Error Message Field -->
<div class="form-group col-sm-6">
    {!! Form::label('internal_error_message', 'Internal Error Message:') !!}
    {!! Form::text('internal_error_message', null, ['class' => 'form-control']) !!}
</div>

<!-- Latest Measurement At Field -->
<div class="form-group col-sm-6">
    {!! Form::label('latest_measurement_at', 'Latest Measurement At:') !!}
    {!! Form::date('latest_measurement_at', null, ['class' => 'form-control','id'=>'latest_measurement_at']) !!}
</div>

@push('scripts')
    <script type="text/javascript">
        $('#latest_measurement_at').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss',
            useCurrent: false
        })
    </script>
@endpush

<!-- Number Of Measurements Field -->
<div class="form-group col-sm-6">
    {!! Form::label('number_of_measurements', 'Number Of Measurements:') !!}
    {!! Form::number('number_of_measurements', null, ['class' => 'form-control']) !!}
</div>

<!-- Reason For Import Field -->
<div class="form-group col-sm-6">
    {!! Form::label('reason_for_import', 'Reason For Import:') !!}
    {!! Form::text('reason_for_import', null, ['class' => 'form-control']) !!}
</div>

<!-- Success Field -->
<div class="form-group col-sm-6">
    {!! Form::label('success', 'Success:') !!}
    <label class="checkbox-inline">
        {!! Form::hidden('success', 0) !!}
        {!! Form::checkbox('success', '1', null) !!}
    </label>
</div>


<!-- User Error Message Field -->
<div class="form-group col-sm-6">
    {!! Form::label('user_error_message', 'User Error Message:') !!}
    {!! Form::text('user_error_message', null, ['class' => 'form-control']) !!}
</div>

<!-- User Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('user_id', 'User Id:') !!}
    {!! Form::number('user_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Additional Meta Data Field -->
<div class="form-group col-sm-12 col-lg-12">
    {!! Form::label('additional_meta_data', 'Additional Meta Data:') !!}
    {!! Form::textarea('additional_meta_data', null, ['class' => 'form-control']) !!}
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    <a href="{{ route('datalab.connectorImports.index') }}" class="btn btn-default">Cancel</a>
</div>
