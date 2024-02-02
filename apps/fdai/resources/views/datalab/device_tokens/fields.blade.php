<!-- User Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('user_id', 'User Id:') !!}
    {!! Form::number('user_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Number Of Waiting Tracking Reminder Notifications Field -->
<div class="form-group col-sm-6">
    {!! Form::label('number_of_waiting_tracking_reminder_notifications', 'Number Of Waiting Tracking Reminder Notifications:') !!}
    {!! Form::number('number_of_waiting_tracking_reminder_notifications', null, ['class' => 'form-control']) !!}
</div>

<!-- Last Notified At Field -->
<div class="form-group col-sm-6">
    {!! Form::label('last_notified_at', 'Last Notified At:') !!}
    {!! Form::date('last_notified_at', null, ['class' => 'form-control','id'=>'last_notified_at']) !!}
</div>

@push('scripts')
    <script type="text/javascript">
        $('#last_notified_at').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss',
            useCurrent: false
        })
    </script>
@endpush

<!-- Platform Field -->
<div class="form-group col-sm-6">
    {!! Form::label('platform', 'Platform:') !!}
    {!! Form::text('platform', null, ['class' => 'form-control']) !!}
</div>

<!-- Number Of New Tracking Reminder Notifications Field -->
<div class="form-group col-sm-6">
    {!! Form::label('number_of_new_tracking_reminder_notifications', 'Number Of New Tracking Reminder Notifications:') !!}
    {!! Form::number('number_of_new_tracking_reminder_notifications', null, ['class' => 'form-control']) !!}
</div>

<!-- Number Of Notifications Last Sent Field -->
<div class="form-group col-sm-6">
    {!! Form::label('number_of_notifications_last_sent', 'Number Of Notifications Last Sent:') !!}
    {!! Form::number('number_of_notifications_last_sent', null, ['class' => 'form-control']) !!}
</div>

<!-- Error Message Field -->
<div class="form-group col-sm-6">
    {!! Form::label('error_message', 'Error Message:') !!}
    {!! Form::text('error_message', null, ['class' => 'form-control']) !!}
</div>

<!-- Last Checked At Field -->
<div class="form-group col-sm-6">
    {!! Form::label('last_checked_at', 'Last Checked At:') !!}
    {!! Form::date('last_checked_at', null, ['class' => 'form-control','id'=>'last_checked_at']) !!}
</div>

@push('scripts')
    <script type="text/javascript">
        $('#last_checked_at').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss',
            useCurrent: false
        })
    </script>
@endpush

<!-- Received At Field -->
<div class="form-group col-sm-6">
    {!! Form::label('received_at', 'Received At:') !!}
    {!! Form::date('received_at', null, ['class' => 'form-control','id'=>'received_at']) !!}
</div>

@push('scripts')
    <script type="text/javascript">
        $('#received_at').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss',
            useCurrent: false
        })
    </script>
@endpush

<!-- Server Ip Field -->
<div class="form-group col-sm-6">
    {!! Form::label('server_ip', 'Server Ip:') !!}
    {!! Form::text('server_ip', null, ['class' => 'form-control']) !!}
</div>

<!-- Server Hostname Field -->
<div class="form-group col-sm-6">
    {!! Form::label('server_hostname', 'Server Hostname:') !!}
    {!! Form::text('server_hostname', null, ['class' => 'form-control']) !!}
</div>

<!-- Client Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('client_id', 'Client Id:') !!}
    {!! Form::text('client_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    <a href="{{ route('datalab.deviceTokens.index') }}" class="btn btn-default">Cancel</a>
</div>
