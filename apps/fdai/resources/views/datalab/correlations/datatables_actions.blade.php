{!! Form::open(['route' => ['datalab.user_variable_relationships.destroy', $id], 'method' => 'delete']) !!}
<div class='btn-group'>
    <a href="{{ route('datalab.user_variable_relationships.show', $id) }}" class='btn btn-sm btn-default'>
        <i class="glyphicon glyphicon-eye-open" title="Open"></i>
    </a>
    <a href="{{ route('datalab.user_variable_relationships.edit', $id) }}" class='btn btn-sm btn-default'>
        <i class="glyphicon glyphicon-edit" title="Edit"></i>
    </a>
    {!! Form::button('<i class="glyphicon glyphicon-trash" title="Delete"></i>', [
        'type' => 'submit',
        'class' => 'btn btn-sm btn-danger',
        'onclick' => "return confirm('Are you sure?')"
    ]) !!}
</div>
{!! Form::close() !!}
