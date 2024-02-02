<!-- Organization Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('organization_id', 'Organization Id:') !!}
    {!! Form::number('organization_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Client Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('client_id', 'Client Id:') !!}
    {!! Form::text('client_id', null, ['class' => 'form-control']) !!}
</div>

<!-- App Display Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('app_display_name', 'App Display Name:') !!}
    {!! Form::text('app_display_name', null, ['class' => 'form-control']) !!}
</div>

<!-- App Description Field -->
<div class="form-group col-sm-6">
    {!! Form::label('app_description', 'App Description:') !!}
    {!! Form::text('app_description', null, ['class' => 'form-control']) !!}
</div>

<!-- Long Description Field -->
<div class="form-group col-sm-12 col-lg-12">
    {!! Form::label('long_description', 'Long Description:') !!}
    {!! Form::textarea('long_description', null, ['class' => 'form-control']) !!}
</div>

<!-- User Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('user_id', 'User Id:') !!}
    {!! Form::number('user_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Icon Url Field -->
<div class="form-group col-sm-6">
    {!! Form::label('icon_url', 'Icon Url:') !!}
    {!! Form::text('icon_url', null, ['class' => 'form-control']) !!}
</div>

<!-- Text Logo Field -->
<div class="form-group col-sm-6">
    {!! Form::label('text_logo', 'Text Logo:') !!}
    {!! Form::text('text_logo', null, ['class' => 'form-control']) !!}
</div>

<!-- Splash Screen Field -->
<div class="form-group col-sm-6">
    {!! Form::label('splash_screen', 'Splash Screen:') !!}
    {!! Form::text('splash_screen', null, ['class' => 'form-control']) !!}
</div>

<!-- Homepage Url Field -->
<div class="form-group col-sm-6">
    {!! Form::label('homepage_url', 'Homepage Url:') !!}
    {!! Form::text('homepage_url', null, ['class' => 'form-control']) !!}
</div>

<!-- App Type Field -->
<div class="form-group col-sm-6">
    {!! Form::label('app_type', 'App Type:') !!}
    {!! Form::text('app_type', null, ['class' => 'form-control']) !!}
</div>

<!-- App Design Field -->
<div class="form-group col-sm-12 col-lg-12">
    {!! Form::label('app_design', 'App Design:') !!}
    {!! Form::textarea('app_design', null, ['class' => 'form-control']) !!}
</div>

<!-- Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('status', 'Status:') !!}
    {!! Form::text('status', null, ['class' => 'form-control']) !!}
</div>

<!-- Enabled Field -->
<div class="form-group col-sm-6">
    {!! Form::label('enabled', 'Enabled:') !!}
    <label class="checkbox-inline">
        {!! Form::hidden('enabled', 0) !!}
        {!! Form::checkbox('enabled', '1', null) !!}
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

<!-- Company Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('company_name', 'Company Name:') !!}
    {!! Form::text('company_name', null, ['class' => 'form-control']) !!}
</div>

<!-- Country Field -->
<div class="form-group col-sm-6">
    {!! Form::label('country', 'Country:') !!}
    {!! Form::text('country', null, ['class' => 'form-control']) !!}
</div>

<!-- Address Field -->
<div class="form-group col-sm-6">
    {!! Form::label('address', 'Address:') !!}
    {!! Form::text('address', null, ['class' => 'form-control']) !!}
</div>

<!-- State Field -->
<div class="form-group col-sm-6">
    {!! Form::label('state', 'State:') !!}
    {!! Form::text('state', null, ['class' => 'form-control']) !!}
</div>

<!-- City Field -->
<div class="form-group col-sm-6">
    {!! Form::label('city', 'City:') !!}
    {!! Form::text('city', null, ['class' => 'form-control']) !!}
</div>

<!-- Zip Field -->
<div class="form-group col-sm-6">
    {!! Form::label('zip', 'Zip:') !!}
    {!! Form::text('zip', null, ['class' => 'form-control']) !!}
</div>

<!-- Plan Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('plan_id', 'Plan Id:') !!}
    {!! Form::number('plan_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Exceeding Call Count Field -->
<div class="form-group col-sm-6">
    {!! Form::label('exceeding_call_count', 'Exceeding Call Count:') !!}
    {!! Form::number('exceeding_call_count', null, ['class' => 'form-control']) !!}
</div>

<!-- Exceeding Call Charge Field -->
<div class="form-group col-sm-6">
    {!! Form::label('exceeding_call_charge', 'Exceeding Call Charge:') !!}
    {!! Form::number('exceeding_call_charge', null, ['class' => 'form-control']) !!}
</div>

<!-- Study Field -->
<div class="form-group col-sm-6">
    {!! Form::label('study', 'Study:') !!}
    <label class="checkbox-inline">
        {!! Form::hidden('study', 0) !!}
        {!! Form::checkbox('study', '1', null) !!}
    </label>
</div>


<!-- Billing Enabled Field -->
<div class="form-group col-sm-6">
    {!! Form::label('billing_enabled', 'Billing Enabled:') !!}
    <label class="checkbox-inline">
        {!! Form::hidden('billing_enabled', 0) !!}
        {!! Form::checkbox('billing_enabled', '1', null) !!}
    </label>
</div>


<!-- Outcome Variable Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('outcome_variable_id', 'Outcome Variable Id:') !!}
    {!! Form::number('outcome_variable_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Predictor Variable Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('predictor_variable_id', 'Predictor Variable Id:') !!}
    {!! Form::number('predictor_variable_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Physician Field -->
<div class="form-group col-sm-6">
    {!! Form::label('physician', 'Physician:') !!}
    <label class="checkbox-inline">
        {!! Form::hidden('physician', 0) !!}
        {!! Form::checkbox('physician', '1', null) !!}
    </label>
</div>


<!-- Additional Settings Field -->
<div class="form-group col-sm-12 col-lg-12">
    {!! Form::label('additional_settings', 'Additional Settings:') !!}
    {!! Form::textarea('additional_settings', null, ['class' => 'form-control']) !!}
</div>

<!-- App Status Field -->
<div class="form-group col-sm-12 col-lg-12">
    {!! Form::label('app_status', 'App Status:') !!}
    {!! Form::textarea('app_status', null, ['class' => 'form-control']) !!}
</div>

<!-- Build Enabled Field -->
<div class="form-group col-sm-6">
    {!! Form::label('build_enabled', 'Build Enabled:') !!}
    <label class="checkbox-inline">
        {!! Form::hidden('build_enabled', 0) !!}
        {!! Form::checkbox('build_enabled', '1', null) !!}
    </label>
</div>


<!-- Wp Post Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('wp_post_id', 'Wp Post Id:') !!}
    {!! Form::number('wp_post_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    <a href="{{ route('datalab.applications.index') }}" class="btn btn-default">Cancel</a>
</div>
