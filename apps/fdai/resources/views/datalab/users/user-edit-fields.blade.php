<!-- User Email Field -->
<div class="form-group col-sm-6">
    {!! Form::label('user_email', 'User Email:') !!}
    {!! Form::email('user_email', null, ['class' => 'form-control']) !!}
</div>

<!-- User Pass Field -->
<div class="form-group col-sm-6">
    {!! Form::label('user_pass', 'User Pass:') !!}
    {!! Form::password('user_pass', ['class' => 'form-control']) !!}
</div>

<!-- User Nicename Field -->
<div class="form-group col-sm-6">
    {!! Form::label('user_nicename', 'User Nicename:') !!}
    {!! Form::text('user_nicename', null, ['class' => 'form-control']) !!}
</div>

<!-- User Url Field -->
<div class="form-group col-sm-6">
    {!! Form::label('user_url', 'User Url:') !!}
    {!! Form::text('user_url', null, ['class' => 'form-control']) !!}
</div>

<!-- Display Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('display_name', 'Display Name:') !!}
    {!! Form::text('display_name', null, ['class' => 'form-control']) !!}
</div>

<!-- Avatar Image Field -->
<div class="form-group col-sm-6">
    {!! Form::label('avatar_image', 'Avatar Image:') !!}
    {!! Form::text('avatar_image', null, ['class' => 'form-control']) !!}
</div>

<!-- Unsubscribed Field -->
<div class="form-group col-sm-6">
    {!! Form::label('unsubscribed', 'Unsubscribed:') !!}
    <label class="checkbox-inline">
        {!! Form::hidden('unsubscribed', 0) !!}
        {!! Form::checkbox('unsubscribed', '1', null) !!}
    </label>
</div>

<!-- Time Zone Offset Field -->
<div class="form-group col-sm-6">
    {!! Form::label('time_zone_offset', 'Time Zone Offset:') !!}
    {!! Form::number('time_zone_offset', null, ['class' => 'form-control']) !!}
</div>

<!-- Earliest Reminder Time Field -->
<div class="form-group col-sm-6">
    {!! Form::label('earliest_reminder_time', 'Earliest Reminder Time:') !!}
    {!! Form::text('earliest_reminder_time', null, ['class' => 'form-control']) !!}
</div>

<!-- Latest Reminder Time Field -->
<div class="form-group col-sm-6">
    {!! Form::label('latest_reminder_time', 'Latest Reminder Time:') !!}
    {!! Form::text('latest_reminder_time', null, ['class' => 'form-control']) !!}
</div>

<!-- Push Notifications Enabled Field -->
<div class="form-group col-sm-6">
    {!! Form::label('push_notifications_enabled', 'Push Notifications Enabled:') !!}
    <label class="checkbox-inline">
        {!! Form::hidden('push_notifications_enabled', 0) !!}
        {!! Form::checkbox('push_notifications_enabled', '1', null) !!}
    </label>
</div>


<!-- Track Location Field -->
<div class="form-group col-sm-6">
    {!! Form::label('track_location', 'Track Location:') !!}
    <label class="checkbox-inline">
        {!! Form::hidden('track_location', 0) !!}
        {!! Form::checkbox('track_location', '1', null) !!}
    </label>
</div>


<!-- Combine Notifications Field -->
<div class="form-group col-sm-6">
    {!! Form::label('combine_notifications', 'Combine Notifications:') !!}
    <label class="checkbox-inline">
        {!! Form::hidden('combine_notifications', 0) !!}
        {!! Form::checkbox('combine_notifications', '1', null) !!}
    </label>
</div>


<!-- Send Reminder Notification Emails Field -->
<div class="form-group col-sm-6">
    {!! Form::label('send_reminder_notification_emails', 'Send Reminder Notification Emails:') !!}
    <label class="checkbox-inline">
        {!! Form::hidden('send_reminder_notification_emails', 0) !!}
        {!! Form::checkbox('send_reminder_notification_emails', '1', null) !!}
    </label>
</div>


<!-- Send Predictor Emails Field -->
<div class="form-group col-sm-6">
    {!! Form::label('send_predictor_emails', 'Send Predictor Emails:') !!}
    <label class="checkbox-inline">
        {!! Form::hidden('send_predictor_emails', 0) !!}
        {!! Form::checkbox('send_predictor_emails', '1', null) !!}
    </label>
</div>


<!-- Get Preview Builds Field -->
<div class="form-group col-sm-6">
    {!! Form::label('get_preview_builds', 'Get Preview Builds:') !!}
    <label class="checkbox-inline">
        {!! Form::hidden('get_preview_builds', 0) !!}
        {!! Form::checkbox('get_preview_builds', '1', null) !!}
    </label>
</div>

<!-- Sms Notifications Enabled Field -->
<div class="form-group col-sm-6">
    {!! Form::label('sms_notifications_enabled', 'Sms Notifications Enabled:') !!}
    <label class="checkbox-inline">
        {!! Form::hidden('sms_notifications_enabled', 0) !!}
        {!! Form::checkbox('sms_notifications_enabled', '1', null) !!}
    </label>
</div>

<!-- Phone Number Field -->
<div class="form-group col-sm-6">
    {!! Form::label('phone_number', 'Phone Number:') !!}
    {!! Form::text('phone_number', null, ['class' => 'form-control']) !!}
</div>

<!-- Has Android App Field -->
<div class="form-group col-sm-6">
    {!! Form::label('has_android_app', 'Has Android App:') !!}
    <label class="checkbox-inline">
        {!! Form::hidden('has_android_app', 0) !!}
        {!! Form::checkbox('has_android_app', '1', null) !!}
    </label>
</div>


<!-- Has Ios App Field -->
<div class="form-group col-sm-6">
    {!! Form::label('has_ios_app', 'Has Ios App:') !!}
    <label class="checkbox-inline">
        {!! Form::hidden('has_ios_app', 0) !!}
        {!! Form::checkbox('has_ios_app', '1', null) !!}
    </label>
</div>


<!-- Has Chrome Extension Field -->
<div class="form-group col-sm-6">
    {!! Form::label('has_chrome_extension', 'Has Chrome Extension:') !!}
    <label class="checkbox-inline">
        {!! Form::hidden('has_chrome_extension', 0) !!}
        {!! Form::checkbox('has_chrome_extension', '1', null) !!}
    </label>
</div>


<!-- Referrer User Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('referrer_user_id', 'Referrer User Id:') !!}
    {!! Form::number('referrer_user_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Address Field -->
<div class="form-group col-sm-6">
    {!! Form::label('address', 'Address:') !!}
    {!! Form::text('address', null, ['class' => 'form-control']) !!}
</div>

<!-- Birthday Field -->
<div class="form-group col-sm-6">
    {!! Form::label('birthday', 'Birthday:') !!}
    {!! Form::text('birthday', null, ['class' => 'form-control']) !!}
</div>

<!-- Country Field -->
<div class="form-group col-sm-6">
    {!! Form::label('country', 'Country:') !!}
    {!! Form::text('country', null, ['class' => 'form-control']) !!}
</div>

<!-- Cover Photo Field -->
<div class="form-group col-sm-6">
    {!! Form::label('cover_photo', 'Cover Photo:') !!}
    {!! Form::text('cover_photo', null, ['class' => 'form-control']) !!}
</div>

<!-- Currency Field -->
<div class="form-group col-sm-6">
    {!! Form::label('currency', 'Currency:') !!}
    {!! Form::text('currency', null, ['class' => 'form-control']) !!}
</div>

<!-- First Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('first_name', 'First Name:') !!}
    {!! Form::text('first_name', null, ['class' => 'form-control']) !!}
</div>

<!-- Gender Field -->
<div class="form-group col-sm-6">
    {!! Form::label('gender', 'Gender:') !!}
    {!! Form::text('gender', null, ['class' => 'form-control']) !!}
</div>

<!-- Language Field -->
<div class="form-group col-sm-6">
    {!! Form::label('language', 'Language:') !!}
    {!! Form::text('language', null, ['class' => 'form-control']) !!}
</div>

<!-- Last Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('last_name', 'Last Name:') !!}
    {!! Form::text('last_name', null, ['class' => 'form-control']) !!}
</div>

<!-- State Field -->
<div class="form-group col-sm-6">
    {!! Form::label('state', 'State:') !!}
    {!! Form::text('state', null, ['class' => 'form-control']) !!}
</div>

<!-- Tag Line Field -->
<div class="form-group col-sm-6">
    {!! Form::label('tag_line', 'Tag Line:') !!}
    {!! Form::text('tag_line', null, ['class' => 'form-control']) !!}
</div>

<!-- Zip Code Field -->
<div class="form-group col-sm-6">
    {!! Form::label('zip_code', 'Zip Code:') !!}
    {!! Form::text('zip_code', null, ['class' => 'form-control']) !!}
</div>


<!-- Timezone Field -->
<div class="form-group col-sm-6">
    {!! Form::label('timezone', 'Timezone:') !!}
    {!! Form::text('timezone', null, ['class' => 'form-control']) !!}
</div>


<!-- Primary Outcome Variable Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('primary_outcome_variable_id', 'Primary Outcome Variable Id:') !!}
    {!! Form::number('primary_outcome_variable_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    <a href="{{ route('datalab.users.index') }}" class="btn btn-default">Cancel</a>
</div>
