@if( isset($errors) && count( $errors ) > 0 )
    <ul id="error-messages" class="alert alert-danger" style="list-style-type: none">
        @foreach( $errors->all() as $error)
            <li>{!! $error !!}</li>
        @endforeach
    </ul>
@elseif( Request::get('error_message') )
    <ul id="error-messages" class="alert alert-danger" style="list-style-type: none">
        <li>{{ app('request')->input('error_message') }}</li>
    </ul>
@elseif( isset($error_message) )
    <ul id="error-messages" class="alert alert-danger" style="list-style-type: none">
        <li>{{ $error_message }}</li>
    </ul>
@endif