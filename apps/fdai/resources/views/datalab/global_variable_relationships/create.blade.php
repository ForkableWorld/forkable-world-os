@extends('layouts.admin-lte-app', ['title' => 'Global Variable Relationships' ])

@section('content')
    <section class="content-header">
        <h1>
            Global Variable Relationship
        </h1>
         @include('single-model-menu-button')
   </section>
   <div class="content">
        @include('adminlte-templates::common.errors')
        <div class="box box-primary">
            <div class="box-body">
                <div class="row">
                    {!! Form::open(['route' => 'datalab.aggregateCorrelations.store']) !!}

                        @include('datalab.global_variable_relationships.fields')

                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@endsection
