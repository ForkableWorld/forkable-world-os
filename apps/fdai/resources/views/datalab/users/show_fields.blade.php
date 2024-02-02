<!-- Client Id Field -->
<div class="form-group">
    {!! Form::label('client_id', 'Client Id:') !!}
    <p>{{ $user->client_id }}</p>
</div>

<!-- User Login Field -->
<div class="form-group">
    {!! Form::label('user_login', 'User Login:') !!}
    <p>{{ $user->user_login }}</p>
</div>

<!-- User Email Field -->
<div class="form-group">
    {!! Form::label('user_email', 'User Email:') !!}
    <p>{{ $user->user_email }}</p>
</div>

<!-- User Pass Field -->
<div class="form-group">
    {!! Form::label('user_pass', 'User Pass:') !!}
    <p>{{ $user->user_pass }}</p>
</div>

<!-- User Nicename Field -->
<div class="form-group">
    {!! Form::label('user_nicename', 'User Nicename:') !!}
    <p>{{ $user->user_nicename }}</p>
</div>

<!-- User Url Field -->
<div class="form-group">
    {!! Form::label('user_url', 'User Url:') !!}
    <p>{{ $user->user_url }}</p>
</div>

<!-- User Registered Field -->
<div class="form-group">
    {!! Form::label('user_registered', 'User Registered:') !!}
    <p>{{ $user->user_registered }}</p>
</div>

<!-- User Activation Key Field -->
<div class="form-group">
    {!! Form::label('user_activation_key', 'User Activation Key:') !!}
    <p>{{ $user->user_activation_key }}</p>
</div>

<!-- User Status Field -->
<div class="form-group">
    {!! Form::label('user_status', 'User Status:') !!}
    <p>{{ $user->user_status }}</p>
</div>

<!-- Display Name Field -->
<div class="form-group">
    {!! Form::label('display_name', 'Display Name:') !!}
    <p>{{ $user->display_name }}</p>
</div>

<!-- Avatar Image Field -->
<div class="form-group">
    {!! Form::label('avatar_image', 'Avatar Image:') !!}
    <p>{{ $user->avatar_image }}</p>
</div>

<!-- Reg Provider Field -->
<div class="form-group">
    {!! Form::label('reg_provider', 'Reg Provider:') !!}
    <p>{{ $user->reg_provider }}</p>
</div>

<!-- Provider Id Field -->
<div class="form-group">
    {!! Form::label('provider_id', 'Provider Id:') !!}
    <p>{{ $user->provider_id }}</p>
</div>

<!-- Provider Token Field -->
<div class="form-group">
    {!! Form::label('provider_token', 'Provider Token:') !!}
    <p>{{ $user->provider_token }}</p>
</div>

<!-- Remember Token Field -->
<div class="form-group">
    {!! Form::label('remember_token', 'Remember Token:') !!}
    <p>{{ $user->remember_token }}</p>
</div>

<!-- Refresh Token Field -->
<div class="form-group">
    {!! Form::label('refresh_token', 'Refresh Token:') !!}
    <p>{{ $user->refresh_token }}</p>
</div>

<!-- Unsubscribed Field -->
<div class="form-group">
    {!! Form::label('unsubscribed', 'Unsubscribed:') !!}
    <p>{{ $user->unsubscribed }}</p>
</div>

<!-- Old User Field -->
<div class="form-group">
    {!! Form::label('old_user', 'Old User:') !!}
    <p>{{ $user->old_user }}</p>
</div>

<!-- Stripe Active Field -->
<div class="form-group">
    {!! Form::label('stripe_active', 'Stripe Active:') !!}
    <p>{{ $user->stripe_active }}</p>
</div>

<!-- Stripe Id Field -->
<div class="form-group">
    {!! Form::label('stripe_id', 'Stripe Id:') !!}
    <p>{{ $user->stripe_id }}</p>
</div>

<!-- Stripe Subscription Field -->
<div class="form-group">
    {!! Form::label('stripe_subscription', 'Stripe Subscription:') !!}
    <p>{{ $user->stripe_subscription }}</p>
</div>

<!-- Stripe Plan Field -->
<div class="form-group">
    {!! Form::label('stripe_plan', 'Stripe Plan:') !!}
    <p>{{ $user->stripe_plan }}</p>
</div>

<!-- Last Four Field -->
<div class="form-group">
    {!! Form::label('last_four', 'Last Four:') !!}
    <p>{{ $user->last_four }}</p>
</div>

<!-- Trial Ends At Field -->
<div class="form-group">
    {!! Form::label('trial_ends_at', 'Trial Ends At:') !!}
    <p>{{ $user->trial_ends_at }}</p>
</div>

<!-- Subscription Ends At Field -->
<div class="form-group">
    {!! Form::label('subscription_ends_at', 'Subscription Ends At:') !!}
    <p>{{ $user->subscription_ends_at }}</p>
</div>

<!-- Roles Field -->
<div class="form-group">
    {!! Form::label('roles', 'Roles:') !!}
    <p>{{ $user->getRolesString() }}</p>
</div>

<!-- Time Zone Offset Field -->
<div class="form-group">
    {!! Form::label('time_zone_offset', 'Time Zone Offset:') !!}
    <p>{{ $user->time_zone_offset }}</p>
</div>

<!-- Earliest Reminder Time Field -->
<div class="form-group">
    {!! Form::label('earliest_reminder_time', 'Earliest Reminder Time:') !!}
    <p>{{ $user->earliest_reminder_time }}</p>
</div>

<!-- Latest Reminder Time Field -->
<div class="form-group">
    {!! Form::label('latest_reminder_time', 'Latest Reminder Time:') !!}
    <p>{{ $user->latest_reminder_time }}</p>
</div>

<!-- Push Notifications Enabled Field -->
<div class="form-group">
    {!! Form::label('push_notifications_enabled', 'Push Notifications Enabled:') !!}
    <p>{{ $user->push_notifications_enabled }}</p>
</div>

<!-- Track Location Field -->
<div class="form-group">
    {!! Form::label('track_location', 'Track Location:') !!}
    <p>{{ $user->track_location }}</p>
</div>

<!-- Combine Notifications Field -->
<div class="form-group">
    {!! Form::label('combine_notifications', 'Combine Notifications:') !!}
    <p>{{ $user->combine_notifications }}</p>
</div>

<!-- Send Reminder Notification Emails Field -->
<div class="form-group">
    {!! Form::label('send_reminder_notification_emails', 'Send Reminder Notification Emails:') !!}
    <p>{{ $user->send_reminder_notification_emails }}</p>
</div>

<!-- Send Predictor Emails Field -->
<div class="form-group">
    {!! Form::label('send_predictor_emails', 'Send Predictor Emails:') !!}
    <p>{{ $user->send_predictor_emails }}</p>
</div>

<!-- Get Preview Builds Field -->
<div class="form-group">
    {!! Form::label('get_preview_builds', 'Get Preview Builds:') !!}
    <p>{{ $user->get_preview_builds }}</p>
</div>

<!-- Subscription Provider Field -->
<div class="form-group">
    {!! Form::label('subscription_provider', 'Subscription Provider:') !!}
    <p>{{ $user->subscription_provider }}</p>
</div>

<!-- Last Sms Tracking Reminder Notification Id Field -->
<div class="form-group">
    {!! Form::label('last_sms_tracking_reminder_notification_id', 'Last Sms Tracking Reminder Notification Id:') !!}
    <p>{{ $user->last_sms_tracking_reminder_notification_id }}</p>
</div>

<!-- Sms Notifications Enabled Field -->
<div class="form-group">
    {!! Form::label('sms_notifications_enabled', 'Sms Notifications Enabled:') !!}
    <p>{{ $user->sms_notifications_enabled }}</p>
</div>

<!-- Phone Verification Code Field -->
<div class="form-group">
    {!! Form::label('phone_verification_code', 'Phone Verification Code:') !!}
    <p>{{ $user->phone_verification_code }}</p>
</div>

<!-- Phone Number Field -->
<div class="form-group">
    {!! Form::label('phone_number', 'Phone Number:') !!}
    <p>{{ $user->phone_number }}</p>
</div>

<!-- Has Android App Field -->
<div class="form-group">
    {!! Form::label('has_android_app', 'Has Android App:') !!}
    <p>{{ $user->has_android_app }}</p>
</div>

<!-- Has Ios App Field -->
<div class="form-group">
    {!! Form::label('has_ios_app', 'Has Ios App:') !!}
    <p>{{ $user->has_ios_app }}</p>
</div>

<!-- Has Chrome Extension Field -->
<div class="form-group">
    {!! Form::label('has_chrome_extension', 'Has Chrome Extension:') !!}
    <p>{{ $user->has_chrome_extension }}</p>
</div>

<!-- Referrer User Id Field -->
<div class="form-group">
    {!! Form::label('referrer_user_id', 'Referrer User Id:') !!}
    <p>{{ $user->referrer_user_id }}</p>
</div>

<!-- Address Field -->
<div class="form-group">
    {!! Form::label('address', 'Address:') !!}
    <p>{{ $user->address }}</p>
</div>

<!-- Birthday Field -->
<div class="form-group">
    {!! Form::label('birthday', 'Birthday:') !!}
    <p>{{ $user->birthday }}</p>
</div>

<!-- Country Field -->
<div class="form-group">
    {!! Form::label('country', 'Country:') !!}
    <p>{{ $user->country }}</p>
</div>

<!-- Cover Photo Field -->
<div class="form-group">
    {!! Form::label('cover_photo', 'Cover Photo:') !!}
    <p>{{ $user->cover_photo }}</p>
</div>

<!-- Currency Field -->
<div class="form-group">
    {!! Form::label('currency', 'Currency:') !!}
    <p>{{ $user->currency }}</p>
</div>

<!-- First Name Field -->
<div class="form-group">
    {!! Form::label('first_name', 'First Name:') !!}
    <p>{{ $user->first_name }}</p>
</div>

<!-- Gender Field -->
<div class="form-group">
    {!! Form::label('gender', 'Gender:') !!}
    <p>{{ $user->gender }}</p>
</div>

<!-- Language Field -->
<div class="form-group">
    {!! Form::label('language', 'Language:') !!}
    <p>{{ $user->language }}</p>
</div>

<!-- Last Name Field -->
<div class="form-group">
    {!! Form::label('last_name', 'Last Name:') !!}
    <p>{{ $user->last_name }}</p>
</div>

<!-- State Field -->
<div class="form-group">
    {!! Form::label('state', 'State:') !!}
    <p>{{ $user->state }}</p>
</div>

<!-- Tag Line Field -->
<div class="form-group">
    {!! Form::label('tag_line', 'Tag Line:') !!}
    <p>{{ $user->tag_line }}</p>
</div>

<!-- Verified Field -->
<div class="form-group">
    {!! Form::label('verified', 'Verified:') !!}
    <p>{{ $user->verified }}</p>
</div>

<!-- Zip Code Field -->
<div class="form-group">
    {!! Form::label('zip_code', 'Zip Code:') !!}
    <p>{{ $user->zip_code }}</p>
</div>

<!-- Spam Field -->
<div class="form-group">
    {!! Form::label('spam', 'Spam:') !!}
    <p>{{ $user->spam }}</p>
</div>

<!-- Deleted Field -->
<div class="form-group">
    {!! Form::label('deleted', 'Deleted:') !!}
    <p>{{ $user->deleted }}</p>
</div>

<!-- Card Brand Field -->
<div class="form-group">
    {!! Form::label('card_brand', 'Card Brand:') !!}
    <p>{{ $user->card_brand }}</p>
</div>

<!-- Card Last Four Field -->
<div class="form-group">
    {!! Form::label('card_last_four', 'Card Last Four:') !!}
    <p>{{ $user->card_last_four }}</p>
</div>

<!-- Last Login At Field -->
<div class="form-group">
    {!! Form::label('last_login_at', 'Last Login At:') !!}
    <p>{{ $user->last_login_at }}</p>
</div>

<!-- Timezone Field -->
<div class="form-group">
    {!! Form::label('timezone', 'Timezone:') !!}
    <p>{{ $user->timezone }}</p>
</div>

<!-- Number Of VariableRelationships Field -->
<div class="form-group">
    {!! Form::label('number_of_correlations', 'Number Of VariableRelationships:') !!}
    <p>{{ $user->number_of_correlations }}</p>
</div>

<!-- Number Of Connections Field -->
<div class="form-group">
    {!! Form::label('number_of_connections', 'Number Of Connections:') !!}
    <p>{{ $user->number_of_connections }}</p>
</div>

<!-- Number Of Tracking Reminders Field -->
<div class="form-group">
    {!! Form::label('number_of_tracking_reminders', 'Number Of Tracking Reminders:') !!}
    <p>{{ $user->number_of_tracking_reminders }}</p>
</div>

<!-- Number Of User Variables Field -->
<div class="form-group">
    {!! Form::label('number_of_user_variables', 'Number Of User Variables:') !!}
    <p>{{ $user->number_of_user_variables }}</p>
</div>

<!-- Number Of Raw Measurements With Tags Field -->
<div class="form-group">
    {!! Form::label('number_of_raw_measurements_with_tags', 'Number Of Raw Measurements With Tags:') !!}
    <p>{{ $user->number_of_raw_measurements_with_tags }}</p>
</div>

<!-- Number Of Raw Measurements With Tags At Last UserVariableRelationship Field -->
<div class="form-group">
    {!! Form::label('number_of_raw_measurements_with_tags_at_last_correlation', 'Number Of Raw Measurements With Tags At Last UserVariableRelationship:') !!}
    <p>{{ $user->number_of_raw_measurements_with_tags_at_last_correlation }}</p>
</div>

<!-- Number Of Votes Field -->
<div class="form-group">
    {!! Form::label('number_of_votes', 'Number Of Votes:') !!}
    <p>{{ $user->number_of_votes }}</p>
</div>

<!-- Number Of Studies Field -->
<div class="form-group">
    {!! Form::label('number_of_studies', 'Number Of Studies:') !!}
    <p>{{ $user->number_of_studies }}</p>
</div>

<!-- Last UserVariableRelationship At Field -->
<div class="form-group">
    {!! Form::label('last_correlation_at', 'Last User Variable Relationship At:') !!}
    <p>{{ $user->last_correlation_at }}</p>
</div>

<!-- Last Email At Field -->
<div class="form-group">
    {!! Form::label('last_email_at', 'Last Email At:') !!}
    <p>{{ $user->last_email_at }}</p>
</div>

<!-- Last Push At Field -->
<div class="form-group">
    {!! Form::label('last_push_at', 'Last Push At:') !!}
    <p>{{ $user->last_push_at }}</p>
</div>

<!-- Primary Outcome Variable Id Field -->
<div class="form-group">
    {!! Form::label('primary_outcome_variable_id', 'Primary Outcome Variable Id:') !!}
    <p>{{ $user->primary_outcome_variable_id }}</p>
</div>

<!-- Wp Post Id Field -->
<div class="form-group">
    {!! Form::label('wp_post_id', 'Wp Post Id:') !!}
    <p>{{ $user->wp_post_id }}</p>
</div>

<!-- Analysis Ended At Field -->
<div class="form-group">
    {!! Form::label('analysis_ended_at', 'Analysis Ended At:') !!}
    <p>{{ $user->analysis_ended_at }}</p>
</div>

<!-- Analysis Requested At Field -->
<div class="form-group">
    {!! Form::label('analysis_requested_at', 'Analysis Requested At:') !!}
    <p>{{ $user->analysis_requested_at }}</p>
</div>

<!-- Analysis Started At Field -->
<div class="form-group">
    {!! Form::label('analysis_started_at', 'Analysis Started At:') !!}
    <p>{{ $user->analysis_started_at }}</p>
</div>

<!-- Internal Error Message Field -->
<div class="form-group">
    {!! Form::label('internal_error_message', 'Internal Error Message:') !!}
    <p>{{ $user->internal_error_message }}</p>
</div>

<!-- Newest Data At Field -->
<div class="form-group">
    {!! Form::label('newest_data_at', 'Newest Data At:') !!}
    <p>{{ $user->newest_data_at }}</p>
</div>

<!-- Reason For Analysis Field -->
<div class="form-group">
    {!! Form::label('reason_for_analysis', 'Reason For Analysis:') !!}
    <p>{{ $user->reason_for_analysis }}</p>
</div>

<!-- User Error Message Field -->
<div class="form-group">
    {!! Form::label('user_error_message', 'User Error Message:') !!}
    <p>{{ $user->user_error_message }}</p>
</div>

<!-- Status Field -->
<div class="form-group">
    {!! Form::label('status', 'Status:') !!}
    <p>{{ $user->status }}</p>
</div>

<!-- Analysis Settings Modified At Field -->
<div class="form-group">
    {!! Form::label('analysis_settings_modified_at', 'Analysis Settings Modified At:') !!}
    <p>{{ $user->analysis_settings_modified_at }}</p>
</div>

