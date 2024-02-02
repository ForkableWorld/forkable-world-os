<!-- User Id Field -->
<div class="form-group">
    {!! Form::label('user_id', 'User Id:') !!}
    <p>{{ $subscription->user_id }}</p>
</div>

<!-- Name Field -->
<div class="form-group">
    {!! Form::label('name', 'Name:') !!}
    <p>{{ $subscription->name }}</p>
</div>

<!-- Stripe Id Field -->
<div class="form-group">
    {!! Form::label('stripe_id', 'Stripe Id:') !!}
    <p>{{ $subscription->stripe_id }}</p>
</div>

<!-- Stripe Plan Field -->
<div class="form-group">
    {!! Form::label('stripe_plan', 'Stripe Plan:') !!}
    <p>{{ $subscription->stripe_plan }}</p>
</div>

<!-- Quantity Field -->
<div class="form-group">
    {!! Form::label('quantity', 'Quantity:') !!}
    <p>{{ $subscription->quantity }}</p>
</div>

<!-- Trial Ends At Field -->
<div class="form-group">
    {!! Form::label('trial_ends_at', 'Trial Ends At:') !!}
    <p>{{ $subscription->trial_ends_at }}</p>
</div>

<!-- Ends At Field -->
<div class="form-group">
    {!! Form::label('ends_at', 'Ends At:') !!}
    <p>{{ $subscription->ends_at }}</p>
</div>

