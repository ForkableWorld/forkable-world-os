<!-- Client Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('client_id', 'Client Id:') !!}
    {!! Form::text('client_id', null, ['class' => 'form-control']) !!}
</div>

<!-- User Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('user_id', 'User Id:') !!}
    {!! Form::number('user_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Expires Field -->
<div class="form-group col-sm-6">
    {!! Form::label('expires', 'Expires:') !!}
    {!! Form::date('expires', null, ['class' => 'form-control','id'=>'expires']) !!}
</div>

@push('scripts')
    <script type="text/javascript">
        $('#expires').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss',
            useCurrent: false
        })
    </script>
@endpush

<!-- Scope Field -->
<div class="form-group col-sm-6">
    {!! Form::label('scope', 'Scope:') !!}
    {!! Form::text('scope', null, ['class' => 'form-control']) !!}
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    <a href="{{ route('datalab.oAuthAccessTokens.index') }}" class="btn btn-default">Cancel</a>
</div>
