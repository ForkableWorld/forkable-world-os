<!-- Name Field -->
<div class="form-group">
    {!! Form::label('name', 'Name:') !!}
    <p>{{ $connector->name }}</p>
</div>

<!-- Display Name Field -->
<div class="form-group">
    {!! Form::label('display_name', 'Display Name:') !!}
    <p>{{ $connector->display_name }}</p>
</div>

<!-- Image Field -->
<div class="form-group">
    {!! Form::label('image', 'Image:') !!}
    <p>{{ $connector->image }}</p>
</div>

<!-- Get It Url Field -->
<div class="form-group">
    {!! Form::label('get_it_url', 'Get It Url:') !!}
    <p>{{ $connector->get_it_url }}</p>
</div>

<!-- Short Description Field -->
<div class="form-group">
    {!! Form::label('short_description', 'Short Description:') !!}
    <p>{{ $connector->short_description }}</p>
</div>

<!-- Long Description Field -->
<div class="form-group">
    {!! Form::label('long_description', 'Long Description:') !!}
    <p>{{ $connector->long_description }}</p>
</div>

<!-- Enabled Field -->
<div class="form-group">
    {!! Form::label('enabled', 'Enabled:') !!}
    <p>{{ $connector->enabled }}</p>
</div>

<!-- Oauth Field -->
<div class="form-group">
    {!! Form::label('oauth', 'Oauth:') !!}
    <p>{{ $connector->oauth }}</p>
</div>

<!-- Qm Client Field -->
<div class="form-group">
    {!! Form::label('qm_client', 'Qm Client:') !!}
    <p>{{ $connector->qm_client }}</p>
</div>

<!-- Client Id Field -->
<div class="form-group">
    {!! Form::label('client_id', 'Client Id:') !!}
    <p>{{ $connector->client_id }}</p>
</div>

<!-- Wp Post Id Field -->
<div class="form-group">
    {!! Form::label('wp_post_id', 'Wp Post Id:') !!}
    <p>{{ $connector->wp_post_id }}</p>
</div>

