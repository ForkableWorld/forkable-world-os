@include('adminlte-templates::common.errors')
<div class="box box-primary">
    <div class="box-body">
        <div class="row">
            {!! Form::model($model, ['route' => ["datalab.$route.update", $model->getId()], 'method' => 'patch']) !!}
            @if(view()->exists("datalab.$viewPath.user-edit-fields"))
                @include("datalab.$viewPath.user-edit-fields")
            @else
                @include("datalab.$viewPath.fields")
            @endif
            {!! Form::close() !!}
        </div>
    </div>
</div>