{!! Form::open(['route' => ['datalab.oAuthAccessTokens.destroy', $access_token], 'method' => 'delete']) !!}
<div class='btn-group'>
    <a href="{{ route('datalab.oAuthAccessTokens.show', $access_token) }}" class='btn btn-default btn-xs'>
        <i class="glyphicon glyphicon-eye-open"></i>
    </a>
    <a href="{{ route('datalab.oAuthAccessTokens.edit', $access_token) }}" class='btn btn-default btn-xs'>
        <i class="glyphicon glyphicon-edit"></i>
    </a>
    {!! Form::button('<i class="glyphicon glyphicon-trash"></i>', [
        'type' => 'submit',
        'class' => 'btn btn-danger btn-xs',
        'onclick' => "return confirm('Are you sure?')"
    ]) !!}
</div>
{!! Form::close() !!}
