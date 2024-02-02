{!! Form::open(['route' => ['datalab.deviceTokens.destroy', $device_token], 'method' => 'delete']) !!}
<div class='btn-group'>
    <a href="{{ route('datalab.deviceTokens.show', $device_token) }}" class='btn btn-default btn-xs'>
        <i class="glyphicon glyphicon-eye-open"></i>
    </a>
    <a href="{{ route('datalab.deviceTokens.edit', $device_token) }}" class='btn btn-default btn-xs'>
        <i class="glyphicon glyphicon-edit"></i>
    </a>
    {!! Form::button('<i class="glyphicon glyphicon-trash"></i>', [
        'type' => 'submit',
        'class' => 'btn btn-danger btn-xs',
        'onclick' => "return confirm('Are you sure?')"
    ]) !!}
</div>
{!! Form::close() !!}
