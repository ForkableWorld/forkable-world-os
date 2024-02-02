<!-- Client Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('client_id', 'Client Id:') !!}
    {!! Form::text('client_id', null, ['class' => 'form-control']) !!}
</div>

<!-- User Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('user_id', 'User Id:') !!}
    {!! Form::number('user_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Connector Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('connector_id', 'Connector Id:') !!}
    {!! Form::number('connector_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Connect Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('connect_status', 'Connect Status:') !!}
    {!! Form::text('connect_status', null, ['class' => 'form-control']) !!}
</div>

<!-- Connect Error Field -->
<div class="form-group col-sm-12 col-lg-12">
    {!! Form::label('connect_error', 'Connect Error:') !!}
    {!! Form::textarea('connect_error', null, ['class' => 'form-control']) !!}
</div>

<!-- Update Requested At Field -->
<div class="form-group col-sm-6">
    {!! Form::label('update_requested_at', 'Update Requested At:') !!}
    {!! Form::date('update_requested_at', null, ['class' => 'form-control','id'=>'update_requested_at']) !!}
</div>

@push('scripts')
    <script type="text/javascript">
        $('#update_requested_at').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss',
            useCurrent: false
        })
    </script>
@endpush

<!-- Update Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('update_status', 'Update Status:') !!}
    {!! Form::text('update_status', null, ['class' => 'form-control']) !!}
</div>

<!-- Update Error Field -->
<div class="form-group col-sm-12 col-lg-12">
    {!! Form::label('update_error', 'Update Error:') !!}
    {!! Form::textarea('update_error', null, ['class' => 'form-control']) !!}
</div>

<!-- Last Successful Updated At Field -->
<div class="form-group col-sm-6">
    {!! Form::label('last_successful_updated_at', 'Last Successful Updated At:') !!}
    {!! Form::date('last_successful_updated_at', null, ['class' => 'form-control','id'=>'last_successful_updated_at']) !!}
</div>

@push('scripts')
    <script type="text/javascript">
        $('#last_successful_updated_at').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss',
            useCurrent: false
        })
    </script>
@endpush

<!-- Total Measurements In Last Update Field -->
<div class="form-group col-sm-6">
    {!! Form::label('total_measurements_in_last_update', 'Total Measurements In Last Update:') !!}
    {!! Form::number('total_measurements_in_last_update', null, ['class' => 'form-control']) !!}
</div>

<!-- User Message Field -->
<div class="form-group col-sm-6">
    {!! Form::label('user_message', 'User Message:') !!}
    {!! Form::text('user_message', null, ['class' => 'form-control']) !!}
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

<!-- Reason For Import Field -->
<div class="form-group col-sm-6">
    {!! Form::label('reason_for_import', 'Reason For Import:') !!}
    {!! Form::text('reason_for_import', null, ['class' => 'form-control']) !!}
</div>

<!-- User Error Message Field -->
<div class="form-group col-sm-6">
    {!! Form::label('user_error_message', 'User Error Message:') !!}
    {!! Form::text('user_error_message', null, ['class' => 'form-control']) !!}
</div>

<!-- Internal Error Message Field -->
<div class="form-group col-sm-6">
    {!! Form::label('internal_error_message', 'Internal Error Message:') !!}
    {!! Form::text('internal_error_message', null, ['class' => 'form-control']) !!}
</div>

<!-- Wp Post Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('wp_post_id', 'Wp Post Id:') !!}
    {!! Form::number('wp_post_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    <a href="{{ route('datalab.connections.index') }}" class="btn btn-default">Cancel</a>
</div>
