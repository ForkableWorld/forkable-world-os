<!-- User Id Field -->
<div class="form-group">
    {!! Form::label('user_id', 'User Id:') !!}
    <p>{{ $trackingReminder->user_id }}</p>
</div>

<!-- Client Id Field -->
<div class="form-group">
    {!! Form::label('client_id', 'Client Id:') !!}
    <p>{{ $trackingReminder->client_id }}</p>
</div>

<!-- Variable Id Field -->
<div class="form-group">
    {!! Form::label('variable_id', 'Variable Id:') !!}
    <p>{{ $trackingReminder->variable_id }}</p>
</div>

<!-- Default Value Field -->
<div class="form-group">
    {!! Form::label('default_value', 'Default Value:') !!}
    <p>{{ $trackingReminder->default_value }}</p>
</div>

<!-- Reminder Start Time Field -->
<div class="form-group">
    {!! Form::label('reminder_start_time', 'Reminder Start Time:') !!}
    <p>{{ $trackingReminder->getReminderStartTimeLocal() }}</p>
</div>

<!-- Reminder End Time Field -->
<div class="form-group">
    {!! Form::label('reminder_end_time', 'Reminder End Time:') !!}
    <p>{{ $trackingReminder->reminder_end_time }}</p>
</div>

<!-- Reminder Sound Field -->
<div class="form-group">
    {!! Form::label('reminder_sound', 'Reminder Sound:') !!}
    <p>{{ $trackingReminder->reminder_sound }}</p>
</div>

<!-- Reminder Frequency Field -->
<div class="form-group">
    {!! Form::label('reminder_frequency', 'Reminder Frequency:') !!}
    <p>{{ $trackingReminder->reminder_frequency }}</p>
</div>

<!-- Pop Up Field -->
<div class="form-group">
    {!! Form::label('pop_up', 'Pop Up:') !!}
    <p>{{ $trackingReminder->pop_up }}</p>
</div>

<!-- Sms Field -->
<div class="form-group">
    {!! Form::label('sms', 'Sms:') !!}
    <p>{{ $trackingReminder->sms }}</p>
</div>

<!-- Email Field -->
<div class="form-group">
    {!! Form::label('email', 'Email:') !!}
    <p>{{ $trackingReminder->email }}</p>
</div>

<!-- Notification Bar Field -->
<div class="form-group">
    {!! Form::label('notification_bar', 'Notification Bar:') !!}
    <p>{{ $trackingReminder->notification_bar }}</p>
</div>

<!-- Last Tracked Field -->
<div class="form-group">
    {!! Form::label('last_tracked', 'Last Tracked:') !!}
    <p>{{ $trackingReminder->last_tracked }}</p>
</div>

<!-- Start Tracking Date Field -->
<div class="form-group">
    {!! Form::label('start_tracking_date', 'Start Tracking Date:') !!}
    <p>{{ $trackingReminder->start_tracking_date }}</p>
</div>

<!-- Stop Tracking Date Field -->
<div class="form-group">
    {!! Form::label('stop_tracking_date', 'Stop Tracking Date:') !!}
    <p>{{ $trackingReminder->stop_tracking_date }}</p>
</div>

<!-- Instructions Field -->
<div class="form-group">
    {!! Form::label('instructions', 'Instructions:') !!}
    <p>{{ $trackingReminder->instructions }}</p>
</div>

<!-- Image Url Field -->
<div class="form-group">
    {!! Form::label('image_url', 'Image Url:') !!}
    <p>{{ $trackingReminder->image_url }}</p>
</div>

<!-- User Variable Id Field -->
<div class="form-group">
    {!! Form::label('user_variable_id', 'User Variable Id:') !!}
    <p>{{ $trackingReminder->user_variable_id }}</p>
</div>

<!-- Latest Tracking Reminder Notification Notify At Field -->
<div class="form-group">
    {!! Form::label('latest_tracking_reminder_notification_notify_at', 'Latest Tracking Reminder Notification Notify At:') !!}
    <p>{{ $trackingReminder->latest_tracking_reminder_notification_notify_at }}</p>
</div>

