<!-- Type Field -->
<div class="form-group">
    {!! Form::label('type', 'Type:') !!}
    <p>{{ $notification->type }}</p>
</div>

<!-- Notifiable Type Field -->
<div class="form-group">
    {!! Form::label('notifiable_type', 'Notifiable Type:') !!}
    <p>{{ $notification->notifiable_type }}</p>
</div>

<!-- Notifiable Id Field -->
<div class="form-group">
    {!! Form::label('notifiable_id', 'Notifiable Id:') !!}
    <p>{{ $notification->notifiable_id }}</p>
</div>

<!-- Data Field -->
<div class="form-group">
    {!! Form::label('data', 'Data:') !!}
    <p>{{ $notification->data }}</p>
</div>

<!-- Read At Field -->
<div class="form-group">
    {!! Form::label('read_at', 'Read At:') !!}
    <p>{{ $notification->read_at }}</p>
</div>

