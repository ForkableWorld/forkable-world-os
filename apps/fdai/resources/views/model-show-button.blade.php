@if( strpos(url()->current(), '/edit') !== false )
    <a class="btn btn-primary pull-right"
       href="{{ route("datalab.".$model->getRouteName().".show", [$model->getId()]) }}">
        <i class="glyphicon glyphicon-eye-open" title="Open"></i>
        &nbsp; View
    </a>
@endif