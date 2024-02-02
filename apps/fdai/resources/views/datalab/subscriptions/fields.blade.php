<!-- User Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('user_id', 'User Id:') !!}
    {!! Form::number('user_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('name', 'Name:') !!}
    {!! Form::text('name', null, ['class' => 'form-control']) !!}
</div>

<!-- Stripe Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('stripe_id', 'Stripe Id:') !!}
    {!! Form::text('stripe_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Stripe Plan Field -->
<div class="form-group col-sm-6">
    {!! Form::label('stripe_plan', 'Stripe Plan:') !!}
    {!! Form::text('stripe_plan', null, ['class' => 'form-control']) !!}
</div>

<!-- Quantity Field -->
<div class="form-group col-sm-6">
    {!! Form::label('quantity', 'Quantity:') !!}
    {!! Form::number('quantity', null, ['class' => 'form-control']) !!}
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

<!-- Ends At Field -->
<div class="form-group col-sm-6">
    {!! Form::label('ends_at', 'Ends At:') !!}
    {!! Form::date('ends_at', null, ['class' => 'form-control','id'=>'ends_at']) !!}
</div>

@push('scripts')
    <script type="text/javascript">
        $('#ends_at').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss',
            useCurrent: false
        })
    </script>
@endpush

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    <a href="{{ route('datalab.subscriptions.index') }}" class="btn btn-default">Cancel</a>
</div>
