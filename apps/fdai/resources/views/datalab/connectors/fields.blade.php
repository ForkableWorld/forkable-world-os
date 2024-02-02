<!-- Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('name', 'Name:') !!}
    {!! Form::text('name', null, ['class' => 'form-control']) !!}
</div>

<!-- Display Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('display_name', 'Display Name:') !!}
    {!! Form::text('display_name', null, ['class' => 'form-control']) !!}
</div>

<!-- Image Field -->
<div class="form-group col-sm-6">
    {!! Form::label('image', 'Image:') !!}
    {!! Form::text('image', null, ['class' => 'form-control']) !!}
</div>

<!-- Get It Url Field -->
<div class="form-group col-sm-6">
    {!! Form::label('get_it_url', 'Get It Url:') !!}
    {!! Form::text('get_it_url', null, ['class' => 'form-control']) !!}
</div>

<!-- Short Description Field -->
<div class="form-group col-sm-12 col-lg-12">
    {!! Form::label('short_description', 'Short Description:') !!}
    {!! Form::textarea('short_description', null, ['class' => 'form-control']) !!}
</div>

<!-- Long Description Field -->
<div class="form-group col-sm-12 col-lg-12">
    {!! Form::label('long_description', 'Long Description:') !!}
    {!! Form::textarea('long_description', null, ['class' => 'form-control']) !!}
</div>

<!-- Enabled Field -->
<div class="form-group col-sm-6">
    {!! Form::label('enabled', 'Enabled:') !!}
    <label class="checkbox-inline">
        {!! Form::hidden('enabled', 0) !!}
        {!! Form::checkbox('enabled', '1', null) !!}
    </label>
</div>


<!-- Oauth Field -->
<div class="form-group col-sm-6">
    {!! Form::label('oauth', 'Oauth:') !!}
    <label class="checkbox-inline">
        {!! Form::hidden('oauth', 0) !!}
        {!! Form::checkbox('oauth', '1', null) !!}
    </label>
</div>


<!-- Qm Client Field -->
<div class="form-group col-sm-6">
    {!! Form::label('qm_client', 'Qm Client:') !!}
    <label class="checkbox-inline">
        {!! Form::hidden('qm_client', 0) !!}
        {!! Form::checkbox('qm_client', '1', null) !!}
    </label>
</div>


<!-- Client Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('client_id', 'Client Id:') !!}
    {!! Form::text('client_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Wp Post Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('wp_post_id', 'Wp Post Id:') !!}
    {!! Form::number('wp_post_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    <a href="{{ route('datalab.connectors.index') }}" class="btn btn-default">Cancel</a>
</div>
