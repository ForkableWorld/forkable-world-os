<!-- Subscriber User Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('subscriber_user_id', 'Subscriber User Id:') !!}
    {!! Form::number('subscriber_user_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Referrer User Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('referrer_user_id', 'Referrer User Id:') !!}
    {!! Form::number('referrer_user_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Subscription Provider Field -->
<div class="form-group col-sm-6">
    {!! Form::label('subscription_provider', 'Subscription Provider:') !!}
    {!! Form::text('subscription_provider', null, ['class' => 'form-control']) !!}
</div>

<!-- Last Four Field -->
<div class="form-group col-sm-6">
    {!! Form::label('last_four', 'Last Four:') !!}
    {!! Form::text('last_four', null, ['class' => 'form-control']) !!}
</div>

<!-- Product Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('product_id', 'Product Id:') !!}
    {!! Form::text('product_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Subscription Provider Transaction Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('subscription_provider_transaction_id', 'Subscription Provider Transaction Id:') !!}
    {!! Form::text('subscription_provider_transaction_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Coupon Field -->
<div class="form-group col-sm-6">
    {!! Form::label('coupon', 'Coupon:') !!}
    {!! Form::text('coupon', null, ['class' => 'form-control']) !!}
</div>

<!-- Client Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('client_id', 'Client Id:') !!}
    {!! Form::text('client_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Refunded At Field -->
<div class="form-group col-sm-6">
    {!! Form::label('refunded_at', 'Refunded At:') !!}
    {!! Form::date('refunded_at', null, ['class' => 'form-control','id'=>'refunded_at']) !!}
</div>

@push('scripts')
    <script type="text/javascript">
        $('#refunded_at').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss',
            useCurrent: false
        })
    </script>
@endpush

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    <a href="{{ route('datalab.purchases.index') }}" class="btn btn-default">Cancel</a>
</div>
