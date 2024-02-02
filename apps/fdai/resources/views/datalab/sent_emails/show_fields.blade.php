<!-- User Id Field -->
<div class="form-group">
    {!! Form::label('user_id', 'User Id:') !!}
    <p>{{ $sentEmail->user_id }}</p>
</div>

<!-- Type Field -->
<div class="form-group">
    {!! Form::label('type', 'Type:') !!}
    <p>{{ $sentEmail->type }}</p>
</div>

<!-- Client Id Field -->
<div class="form-group">
    {!! Form::label('client_id', 'Client Id:') !!}
    <p>{{ $sentEmail->client_id }}</p>
</div>

<!-- Slug Field -->
<div class="form-group">
    {!! Form::label('slug', 'Slug:') !!}
    <p>{{ $sentEmail->slug }}</p>
</div>

<!-- Response Field -->
<div class="form-group">
    {!! Form::label('response', 'Response:') !!}
    <p>{{ $sentEmail->response }}</p>
</div>



<!-- Wp Post Id Field -->
<div class="form-group">
    {!! Form::label('wp_post_id', 'Wp Post Id:') !!}
    <p>{{ $sentEmail->wp_post_id }}</p>
</div>

<!-- Email Address Field -->
<div class="form-group">
    {!! Form::label('email_address', 'Email Address:') !!}
    <p>{{ $sentEmail->email_address }}</p>
</div>

<!-- Content Field -->
<div class="form-group">
    {!! Form::label('content', 'Content:') !!}
    {!! $sentEmail->content !!}
</div>
