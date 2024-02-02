<!-- User Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('user_id', 'User Id:') !!}
    {!! Form::number('user_id', null, ['class' => 'form-control']) !!}
</div>

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

<!-- Default Value Field -->
<div class="form-group col-sm-6">
    {!! Form::label('default_value', 'Default Value:') !!}
    {!! Form::number('default_value', null, ['class' => 'form-control']) !!}
</div>

<!-- Reminder Start Time Field -->
<div class="form-group col-sm-6">
    {!! Form::label('reminder_start_time', 'Reminder Start Time:') !!}
    {!! Form::text('reminder_start_time', null, ['class' => 'form-control']) !!}
</div>

<!-- Reminder End Time Field -->
<div class="form-group col-sm-6">
    {!! Form::label('reminder_end_time', 'Reminder End Time:') !!}
    {!! Form::text('reminder_end_time', null, ['class' => 'form-control']) !!}
</div>

<!-- Reminder Sound Field -->
<div class="form-group col-sm-6">
    {!! Form::label('reminder_sound', 'Reminder Sound:') !!}
    {!! Form::text('reminder_sound', null, ['class' => 'form-control']) !!}
</div>

<!-- Reminder Frequency Field -->
<div class="form-group col-sm-6">
    {!! Form::label('reminder_frequency', 'Reminder Frequency:') !!}
    {!! Form::number('reminder_frequency', null, ['class' => 'form-control']) !!}
</div>

<!-- Pop Up Field -->
<div class="form-group col-sm-6">
    {!! Form::label('pop_up', 'Pop Up:') !!}
    <label class="checkbox-inline">
        {!! Form::hidden('pop_up', 0) !!}
        {!! Form::checkbox('pop_up', '1', null) !!}
    </label>
</div>


<!-- Sms Field -->
<div class="form-group col-sm-6">
    {!! Form::label('sms', 'Sms:') !!}
    <label class="checkbox-inline">
        {!! Form::hidden('sms', 0) !!}
        {!! Form::checkbox('sms', '1', null) !!}
    </label>
</div>


<!-- Email Field -->
<div class="form-group col-sm-6">
    {!! Form::label('email', 'Email:') !!}
    {!! Form::email('email', null, ['class' => 'form-control']) !!}
</div>

<!-- Notification Bar Field -->
<div class="form-group col-sm-6">
    {!! Form::label('notification_bar', 'Notification Bar:') !!}
    <label class="checkbox-inline">
        {!! Form::hidden('notification_bar', 0) !!}
        {!! Form::checkbox('notification_bar', '1', null) !!}
    </label>
</div>

<!-- Start Tracking Date Field -->
<div class="form-group col-sm-6">
    {!! Form::label('start_tracking_date', 'Start Tracking Date:') !!}
    {!! Form::date('start_tracking_date', null, ['class' => 'form-control','id'=>'start_tracking_date']) !!}
</div>

@push('scripts')
    <script type="text/javascript">
        $('#start_tracking_date').datetimepicker({
            format: 'YYYY-MM-DD',
            useCurrent: false
        })
    </script>
@endpush

<!-- Stop Tracking Date Field -->
<div class="form-group col-sm-6">
    {!! Form::label('stop_tracking_date', 'Stop Tracking Date:') !!}
    {!! Form::date('stop_tracking_date', null, ['class' => 'form-control','id'=>'stop_tracking_date']) !!}
</div>

@push('scripts')
    <script type="text/javascript">
        $('#stop_tracking_date').datetimepicker({
            format: 'YYYY-MM-DD',
            useCurrent: false
        })
    </script>
@endpush

<!-- Instructions Field -->
<div class="form-group col-sm-12 col-lg-12">
    {!! Form::label('instructions', 'Instructions:') !!}
    {!! Form::textarea('instructions', null, ['class' => 'form-control']) !!}
</div>

<!-- Unit Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('unit_id', 'Unit Id:') !!}
    {!! Form::number('unit_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Image Url Field -->
<div class="form-group col-sm-6">
    {!! Form::label('image_url', 'Image Url:') !!}
    {!! Form::text('image_url', null, ['class' => 'form-control']) !!}
</div>

<!-- User Variable Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('user_variable_id', 'User Variable Id:') !!}
    {!! Form::number('user_variable_id', null, ['class' => 'form-control']) !!}
</div>


<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    <a href="{{ route('datalab.trackingReminders.index') }}" class="btn btn-default">Cancel</a>
</div>
