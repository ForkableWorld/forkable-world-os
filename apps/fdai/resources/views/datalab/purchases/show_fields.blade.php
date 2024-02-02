<!-- Subscriber User Id Field -->
<div class="form-group">
    {!! Form::label('subscriber_user_id', 'Subscriber User Id:') !!}
    <p>{{ $purchase->subscriber_user_id }}</p>
</div>

<!-- Referrer User Id Field -->
<div class="form-group">
    {!! Form::label('referrer_user_id', 'Referrer User Id:') !!}
    <p>{{ $purchase->referrer_user_id }}</p>
</div>

<!-- Subscription Provider Field -->
<div class="form-group">
    {!! Form::label('subscription_provider', 'Subscription Provider:') !!}
    <p>{{ $purchase->subscription_provider }}</p>
</div>

<!-- Last Four Field -->
<div class="form-group">
    {!! Form::label('last_four', 'Last Four:') !!}
    <p>{{ $purchase->last_four }}</p>
</div>

<!-- Product Id Field -->
<div class="form-group">
    {!! Form::label('product_id', 'Product Id:') !!}
    <p>{{ $purchase->product_id }}</p>
</div>

<!-- Subscription Provider Transaction Id Field -->
<div class="form-group">
    {!! Form::label('subscription_provider_transaction_id', 'Subscription Provider Transaction Id:') !!}
    <p>{{ $purchase->subscription_provider_transaction_id }}</p>
</div>

<!-- Coupon Field -->
<div class="form-group">
    {!! Form::label('coupon', 'Coupon:') !!}
    <p>{{ $purchase->coupon }}</p>
</div>

<!-- Client Id Field -->
<div class="form-group">
    {!! Form::label('client_id', 'Client Id:') !!}
    <p>{{ $purchase->client_id }}</p>
</div>

<!-- Refunded At Field -->
<div class="form-group">
    {!! Form::label('refunded_at', 'Refunded At:') !!}
    <p>{{ $purchase->refunded_at }}</p>
</div>

