<!-- User Id Field -->
<div class="form-group">
    {!! Form::label('user_id', 'User Id:') !!}
    <p>{{ $collaborator->user_id }}</p>
</div>

<!-- App Id Field -->
<div class="form-group">
    {!! Form::label('app_id', 'App Id:') !!}
    <p>{{ $collaborator->app_id }}</p>
</div>

<!-- Type Field -->
<div class="form-group">
    {!! Form::label('type', 'Type:') !!}
    <p>{{ $collaborator->type }}</p>
</div>

<!-- Client Id Field -->
<div class="form-group">
    {!! Form::label('client_id', 'Client Id:') !!}
    <p>{{ $collaborator->client_id }}</p>
</div>

