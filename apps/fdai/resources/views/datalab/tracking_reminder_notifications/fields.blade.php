<!-- Tracking Reminder Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('tracking_reminder_id', 'Tracking Reminder Id:') !!}
    {!! Form::number('tracking_reminder_id', null, ['class' => 'form-control']) !!}
</div>

@push('scripts')
    <script type="text/javascript">
        $('#reminder_time').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss',
            useCurrent: false
        })
    </script>
@endpush

<!-- User Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('user_id', 'User Id:') !!}
    {!! Form::number('user_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Notified At Field -->
<div class="form-group col-sm-6">
    {!! Form::label('notified_at', 'Notified At:') !!}
    {!! Form::date('notified_at', null, ['class' => 'form-control','id'=>'notified_at']) !!}
</div>

@push('scripts')
    <script type="text/javascript">
        $('#notified_at').datetimepicker({
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

<!-- Client Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('client_id', 'Client Id:') !!}
    {!! Form::text('client_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Variable Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('variable_id', 'Variable Id:') !!}
    {!! Form::number('variable_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Notify At Field -->
<div class="form-group col-sm-6">
    {!! Form::label('notify_at', 'Notify At:') !!}
    {!! Form::date('notify_at', null, ['class' => 'form-control','id'=>'notify_at']) !!}
</div>

@push('scripts')
    <script type="text/javascript">
        $('#notify_at').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss',
            useCurrent: false
        })
    </script>
@endpush

<!-- User Variable Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('user_variable_id', 'User Variable Id:') !!}
    {!! Form::number('user_variable_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    <a href="{{ route('datalab.trackingReminderNotifications.index') }}" class="btn btn-default">Cancel</a>
</div>
