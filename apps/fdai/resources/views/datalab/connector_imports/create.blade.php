@extends('layouts.admin-lte-app', ['title' => null ])

@section('content')
    <section class="content-header">
        <h1>
            Connector Import
        </h1>
         @include('single-model-menu-button')
   </section>
   <div class="content">
        @include('adminlte-templates::common.errors')
        <div class="box box-primary">
            <div class="box-body">
                <div class="row">
                    {!! Form::open(['route' => 'datalab.connectorImports.store']) !!}

                        @include('datalab.connector_imports.fields')

                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@endsection
