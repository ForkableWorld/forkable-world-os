<!-- Client Id Field -->
<div class="form-group">
    {!! Form::label('client_id', 'Client Id:') !!}
    <p>{{ $bshafferOauthAccessToken->client_id }}</p>
</div>

<!-- User Id Field -->
<div class="form-group">
    {!! Form::label('user_id', 'User Id:') !!}
    <p>{{ $bshafferOauthAccessToken->user_id }}</p>
</div>

<!-- Expires Field -->
<div class="form-group">
    {!! Form::label('expires', 'Expires:') !!}
    <p>{{ $bshafferOauthAccessToken->expires }}</p>
</div>

<!-- Scope Field -->
<div class="form-group">
    {!! Form::label('scope', 'Scope:') !!}
    <p>{{ $bshafferOauthAccessToken->scope }}</p>
</div>

