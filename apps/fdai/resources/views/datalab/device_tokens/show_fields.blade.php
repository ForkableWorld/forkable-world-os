<!-- User Id Field -->
<div class="form-group">
    {!! Form::label('user_id', 'User Id:') !!}
    <p>{{ $deviceToken->user_id }}</p>
</div>

<!-- Number Of Waiting Tracking Reminder Notifications Field -->
<div class="form-group">
    {!! Form::label('number_of_waiting_tracking_reminder_notifications', 'Number Of Waiting Tracking Reminder Notifications:') !!}
    <p>{{ $deviceToken->number_of_waiting_tracking_reminder_notifications }}</p>
</div>

<!-- Last Notified At Field -->
<div class="form-group">
    {!! Form::label('last_notified_at', 'Last Notified At:') !!}
    <p>{{ $deviceToken->last_notified_at }}</p>
</div>

<!-- Bshaffer Oauth Clients Id Field -->
<div class="form-group">
    {!! Form::label('client_id', 'Client Id:') !!}
    <p>{{ $deviceToken->client_id }}</p>
</div>

<!-- Platform Field -->
<div class="form-group">
    {!! Form::label('platform', 'Platform:') !!}
    <p>{{ $deviceToken->platform }}</p>
</div>

<!-- Number Of New Tracking Reminder Notifications Field -->
<div class="form-group">
    {!! Form::label('number_of_new_tracking_reminder_notifications', 'Number Of New Tracking Reminder Notifications:') !!}
    <p>{{ $deviceToken->number_of_new_tracking_reminder_notifications }}</p>
</div>

<!-- Number Of Notifications Last Sent Field -->
<div class="form-group">
    {!! Form::label('number_of_notifications_last_sent', 'Number Of Notifications Last Sent:') !!}
    <p>{{ $deviceToken->number_of_notifications_last_sent }}</p>
</div>

<!-- Error Message Field -->
<div class="form-group">
    {!! Form::label('error_message', 'Error Message:') !!}
    <p>{{ $deviceToken->error_message }}</p>
</div>

<!-- Last Checked At Field -->
<div class="form-group">
    {!! Form::label('last_checked_at', 'Last Checked At:') !!}
    <p>{{ $deviceToken->last_checked_at }}</p>
</div>

<!-- Received At Field -->
<div class="form-group">
    {!! Form::label('received_at', 'Received At:') !!}
    <p>{{ $deviceToken->received_at }}</p>
</div>

<!-- Server Ip Field -->
<div class="form-group">
    {!! Form::label('server_ip', 'Server Ip:') !!}
    <p>{{ $deviceToken->server_ip }}</p>
</div>

<!-- Server Hostname Field -->
<div class="form-group">
    {!! Form::label('server_hostname', 'Server Hostname:') !!}
    <p>{{ $deviceToken->server_hostname }}</p>
</div>

<!-- Client Id Field -->
<div class="form-group">
    {!! Form::label('client_id', 'Client Id:') !!}
    <p>{{ $deviceToken->client_id }}</p>
</div>

