<!-- Client Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('client_id', 'Client Id:') !!}
    {!! Form::text('client_id', null, ['class' => 'form-control']) !!}
</div>

<!-- User Login Field -->
<div class="form-group col-sm-6">
    {!! Form::label('user_login', 'User Login:') !!}
    {!! Form::text('user_login', null, ['class' => 'form-control']) !!}
</div>

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

<!-- User Registered Field -->
<div class="form-group col-sm-6">
    {!! Form::label('user_registered', 'User Registered:') !!}
    {!! Form::date('user_registered', null, ['class' => 'form-control','id'=>'user_registered']) !!}
</div>

@push('scripts')
    <script type="text/javascript">
        $('#user_registered').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss',
            useCurrent: false
        })
    </script>
@endpush

<!-- User Activation Key Field -->
<div class="form-group col-sm-6">
    {!! Form::label('user_activation_key', 'User Activation Key:') !!}
    {!! Form::text('user_activation_key', null, ['class' => 'form-control']) !!}
</div>

<!-- User Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('user_status', 'User Status:') !!}
    {!! Form::number('user_status', null, ['class' => 'form-control']) !!}
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

<!-- Reg Provider Field -->
<div class="form-group col-sm-6">
    {!! Form::label('reg_provider', 'Reg Provider:') !!}
    {!! Form::text('reg_provider', null, ['class' => 'form-control']) !!}
</div>

<!-- Provider Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('provider_id', 'Provider Id:') !!}
    {!! Form::text('provider_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Provider Token Field -->
<div class="form-group col-sm-6">
    {!! Form::label('provider_token', 'Provider Token:') !!}
    {!! Form::text('provider_token', null, ['class' => 'form-control']) !!}
</div>

<!-- Remember Token Field -->
<div class="form-group col-sm-6">
    {!! Form::label('remember_token', 'Remember Token:') !!}
    {!! Form::text('remember_token', null, ['class' => 'form-control']) !!}
</div>

<!-- Refresh Token Field -->
<div class="form-group col-sm-6">
    {!! Form::label('refresh_token', 'Refresh Token:') !!}
    {!! Form::text('refresh_token', null, ['class' => 'form-control']) !!}
</div>

<!-- Unsubscribed Field -->
<div class="form-group col-sm-6">
    {!! Form::label('unsubscribed', 'Unsubscribed:') !!}
    <label class="checkbox-inline">
        {!! Form::hidden('unsubscribed', 0) !!}
        {!! Form::checkbox('unsubscribed', '1', null) !!}
    </label>
</div>


<!-- Old User Field -->
<div class="form-group col-sm-6">
    {!! Form::label('old_user', 'Old User:') !!}
    <label class="checkbox-inline">
        {!! Form::hidden('old_user', 0) !!}
        {!! Form::checkbox('old_user', '1', null) !!}
    </label>
</div>


<!-- Stripe Active Field -->
<div class="form-group col-sm-6">
    {!! Form::label('stripe_active', 'Stripe Active:') !!}
    <label class="checkbox-inline">
        {!! Form::hidden('stripe_active', 0) !!}
        {!! Form::checkbox('stripe_active', '1', null) !!}
    </label>
</div>


<!-- Stripe Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('stripe_id', 'Stripe Id:') !!}
    {!! Form::text('stripe_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Stripe Subscription Field -->
<div class="form-group col-sm-6">
    {!! Form::label('stripe_subscription', 'Stripe Subscription:') !!}
    {!! Form::text('stripe_subscription', null, ['class' => 'form-control']) !!}
</div>

<!-- Stripe Plan Field -->
<div class="form-group col-sm-6">
    {!! Form::label('stripe_plan', 'Stripe Plan:') !!}
    {!! Form::text('stripe_plan', null, ['class' => 'form-control']) !!}
</div>

<!-- Last Four Field -->
<div class="form-group col-sm-6">
    {!! Form::label('last_four', 'Last Four:') !!}
    {!! Form::text('last_four', null, ['class' => 'form-control']) !!}
</div>

<!-- Trial Ends At Field -->
<div class="form-group col-sm-6">
    {!! Form::label('trial_ends_at', 'Trial Ends At:') !!}
    {!! Form::date('trial_ends_at', null, ['class' => 'form-control','id'=>'trial_ends_at']) !!}
</div>

@push('scripts')
    <script type="text/javascript">
        $('#trial_ends_at').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss',
            useCurrent: false
        })
    </script>
@endpush

<!-- Subscription Ends At Field -->
<div class="form-group col-sm-6">
    {!! Form::label('subscription_ends_at', 'Subscription Ends At:') !!}
    {!! Form::date('subscription_ends_at', null, ['class' => 'form-control','id'=>'subscription_ends_at']) !!}
</div>

@push('scripts')
    <script type="text/javascript">
        $('#subscription_ends_at').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss',
            useCurrent: false
        })
    </script>
@endpush

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


<!-- Subscription Provider Field -->
<div class="form-group col-sm-6">
    {!! Form::label('subscription_provider', 'Subscription Provider:') !!}
    {!! Form::text('subscription_provider', null, ['class' => 'form-control']) !!}
</div>

<!-- Last Sms Tracking Reminder Notification Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('last_sms_tracking_reminder_notification_id', 'Last Sms Tracking Reminder Notification Id:') !!}
    {!! Form::number('last_sms_tracking_reminder_notification_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Sms Notifications Enabled Field -->
<div class="form-group col-sm-6">
    {!! Form::label('sms_notifications_enabled', 'Sms Notifications Enabled:') !!}
    <label class="checkbox-inline">
        {!! Form::hidden('sms_notifications_enabled', 0) !!}
        {!! Form::checkbox('sms_notifications_enabled', '1', null) !!}
    </label>
</div>


<!-- Phone Verification Code Field -->
<div class="form-group col-sm-6">
    {!! Form::label('phone_verification_code', 'Phone Verification Code:') !!}
    {!! Form::text('phone_verification_code', null, ['class' => 'form-control']) !!}
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

<!-- Verified Field -->
<div class="form-group col-sm-6">
    {!! Form::label('verified', 'Verified:') !!}
    {!! Form::text('verified', null, ['class' => 'form-control']) !!}
</div>

<!-- Zip Code Field -->
<div class="form-group col-sm-6">
    {!! Form::label('zip_code', 'Zip Code:') !!}
    {!! Form::text('zip_code', null, ['class' => 'form-control']) !!}
</div>

<!-- Spam Field -->
<div class="form-group col-sm-6">
    {!! Form::label('spam', 'Spam:') !!}
    <label class="checkbox-inline">
        {!! Form::hidden('spam', 0) !!}
        {!! Form::checkbox('spam', '1', null) !!}
    </label>
</div>


<!-- Deleted Field -->
<div class="form-group col-sm-6">
    {!! Form::label('deleted', 'Deleted:') !!}
    <label class="checkbox-inline">
        {!! Form::hidden('deleted', 0) !!}
        {!! Form::checkbox('deleted', '1', null) !!}
    </label>
</div>


<!-- Card Brand Field -->
<div class="form-group col-sm-6">
    {!! Form::label('card_brand', 'Card Brand:') !!}
    {!! Form::text('card_brand', null, ['class' => 'form-control']) !!}
</div>

<!-- Card Last Four Field -->
<div class="form-group col-sm-6">
    {!! Form::label('card_last_four', 'Card Last Four:') !!}
    {!! Form::text('card_last_four', null, ['class' => 'form-control']) !!}
</div>

<!-- Last Login At Field -->
<div class="form-group col-sm-6">
    {!! Form::label('last_login_at', 'Last Login At:') !!}
    {!! Form::date('last_login_at', null, ['class' => 'form-control','id'=>'last_login_at']) !!}
</div>

@push('scripts')
    <script type="text/javascript">
        $('#last_login_at').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss',
            useCurrent: false
        })
    </script>
@endpush

<!-- Timezone Field -->
<div class="form-group col-sm-6">
    {!! Form::label('timezone', 'Timezone:') !!}
    {!! Form::text('timezone', null, ['class' => 'form-control']) !!}
</div>

<!-- Number Of VariableRelationships Field -->
<div class="form-group col-sm-6">
    {!! Form::label('number_of_correlations', 'Number Of VariableRelationships:') !!}
    {!! Form::number('number_of_correlations', null, ['class' => 'form-control']) !!}
</div>

<!-- Number Of Connections Field -->
<div class="form-group col-sm-6">
    {!! Form::label('number_of_connections', 'Number Of Connections:') !!}
    {!! Form::number('number_of_connections', null, ['class' => 'form-control']) !!}
</div>

<!-- Number Of Tracking Reminders Field -->
<div class="form-group col-sm-6">
    {!! Form::label('number_of_tracking_reminders', 'Number Of Tracking Reminders:') !!}
    {!! Form::number('number_of_tracking_reminders', null, ['class' => 'form-control']) !!}
</div>

<!-- Number Of User Variables Field -->
<div class="form-group col-sm-6">
    {!! Form::label('number_of_user_variables', 'Number Of User Variables:') !!}
    {!! Form::number('number_of_user_variables', null, ['class' => 'form-control']) !!}
</div>

<!-- Number Of Raw Measurements With Tags Field -->
<div class="form-group col-sm-6">
    {!! Form::label('number_of_raw_measurements_with_tags', 'Number Of Raw Measurements With Tags:') !!}
    {!! Form::number('number_of_raw_measurements_with_tags', null, ['class' => 'form-control']) !!}
</div>

<!-- Number Of Raw Measurements With Tags At Last UserVariableRelationship Field -->
<div class="form-group col-sm-6">
    {!! Form::label('number_of_raw_measurements_with_tags_at_last_correlation', 'Number Of Raw Measurements With Tags At Last UserVariableRelationship:') !!}
    {!! Form::number('number_of_raw_measurements_with_tags_at_last_correlation', null, ['class' => 'form-control']) !!}
</div>

<!-- Number Of Votes Field -->
<div class="form-group col-sm-6">
    {!! Form::label('number_of_votes', 'Number Of Votes:') !!}
    {!! Form::number('number_of_votes', null, ['class' => 'form-control']) !!}
</div>

<!-- Number Of Studies Field -->
<div class="form-group col-sm-6">
    {!! Form::label('number_of_studies', 'Number Of Studies:') !!}
    {!! Form::number('number_of_studies', null, ['class' => 'form-control']) !!}
</div>

<!-- Last UserVariableRelationship At Field -->
<div class="form-group col-sm-6">
    {!! Form::label('last_correlation_at', 'Last User Variable Relationship At:') !!}
    {!! Form::date('last_correlation_at', null, ['class' => 'form-control','id'=>'last_correlation_at']) !!}
</div>

@push('scripts')
    <script type="text/javascript">
        $('#last_correlation_at').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss',
            useCurrent: false
        })
    </script>
@endpush

<!-- Last Email At Field -->
<div class="form-group col-sm-6">
    {!! Form::label('last_email_at', 'Last Email At:') !!}
    {!! Form::date('last_email_at', null, ['class' => 'form-control','id'=>'last_email_at']) !!}
</div>

@push('scripts')
    <script type="text/javascript">
        $('#last_email_at').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss',
            useCurrent: false
        })
    </script>
@endpush

<!-- Last Push At Field -->
<div class="form-group col-sm-6">
    {!! Form::label('last_push_at', 'Last Push At:') !!}
    {!! Form::date('last_push_at', null, ['class' => 'form-control','id'=>'last_push_at']) !!}
</div>

@push('scripts')
    <script type="text/javascript">
        $('#last_push_at').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss',
            useCurrent: false
        })
    </script>
@endpush

<!-- Primary Outcome Variable Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('primary_outcome_variable_id', 'Primary Outcome Variable Id:') !!}
    {!! Form::number('primary_outcome_variable_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Wp Post Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('wp_post_id', 'Wp Post Id:') !!}
    {!! Form::number('wp_post_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Analysis Ended At Field -->
<div class="form-group col-sm-6">
    {!! Form::label('analysis_ended_at', 'Analysis Ended At:') !!}
    {!! Form::date('analysis_ended_at', null, ['class' => 'form-control','id'=>'analysis_ended_at']) !!}
</div>

@push('scripts')
    <script type="text/javascript">
        $('#analysis_ended_at').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss',
            useCurrent: false
        })
    </script>
@endpush

<!-- Analysis Requested At Field -->
<div class="form-group col-sm-6">
    {!! Form::label('analysis_requested_at', 'Analysis Requested At:') !!}
    {!! Form::date('analysis_requested_at', null, ['class' => 'form-control','id'=>'analysis_requested_at']) !!}
</div>

@push('scripts')
    <script type="text/javascript">
        $('#analysis_requested_at').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss',
            useCurrent: false
        })
    </script>
@endpush

<!-- Analysis Started At Field -->
<div class="form-group col-sm-6">
    {!! Form::label('analysis_started_at', 'Analysis Started At:') !!}
    {!! Form::date('analysis_started_at', null, ['class' => 'form-control','id'=>'analysis_started_at']) !!}
</div>

@push('scripts')
    <script type="text/javascript">
        $('#analysis_started_at').datetimepicker({
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

<!-- Newest Data At Field -->
<div class="form-group col-sm-6">
    {!! Form::label('newest_data_at', 'Newest Data At:') !!}
    {!! Form::date('newest_data_at', null, ['class' => 'form-control','id'=>'newest_data_at']) !!}
</div>

@push('scripts')
    <script type="text/javascript">
        $('#newest_data_at').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss',
            useCurrent: false
        })
    </script>
@endpush

<!-- Reason For Analysis Field -->
<div class="form-group col-sm-6">
    {!! Form::label('reason_for_analysis', 'Reason For Analysis:') !!}
    {!! Form::text('reason_for_analysis', null, ['class' => 'form-control']) !!}
</div>

<!-- User Error Message Field -->
<div class="form-group col-sm-6">
    {!! Form::label('user_error_message', 'User Error Message:') !!}
    {!! Form::text('user_error_message', null, ['class' => 'form-control']) !!}
</div>

<!-- Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('status', 'Status:') !!}
    {!! Form::text('status', null, ['class' => 'form-control']) !!}
</div>

<!-- Analysis Settings Modified At Field -->
<div class="form-group col-sm-6">
    {!! Form::label('analysis_settings_modified_at', 'Analysis Settings Modified At:') !!}
    {!! Form::date('analysis_settings_modified_at', null, ['class' => 'form-control','id'=>'analysis_settings_modified_at']) !!}
</div>

@push('scripts')
    <script type="text/javascript">
        $('#analysis_settings_modified_at').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss',
            useCurrent: false
        })
    </script>
@endpush

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    <a href="{{ route('datalab.users.index') }}" class="btn btn-default">Cancel</a>
</div>
