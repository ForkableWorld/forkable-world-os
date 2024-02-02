<?php /** @var App\Models\MeasurementImport $measurementImport */ ?>
@extends('layouts.admin-lte-app', ['title' => null ])

@section('content')
   @include('model-header')
   <div class="content">
       @include('adminlte-templates::common.errors')
       <div class="box box-primary">
           <div class="box-body">
               <div class="row">
                   {!! Form::model($measurementImport, ['route' => ['datalab.measurementImports.update', $measurementImport->id], 'method' => 'patch']) !!}

                        @include('datalab.measurement_imports.fields')

                   {!! Form::close() !!}
               </div>
           </div>
       </div>
   </div>
@endsection
