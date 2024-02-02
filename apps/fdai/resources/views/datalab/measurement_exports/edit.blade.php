<?php /** @var App\Models\MeasurementExport $measurementExport */ ?>
@extends('layouts.admin-lte-app', ['title' => null ])

@section('content')
   @include('model-header')
   <div class="content">
       @include('adminlte-templates::common.errors')
       <div class="box box-primary">
           <div class="box-body">
               <div class="row">
                   {!! Form::model($measurementExport, ['route' => ['datalab.measurementExports.update', $measurementExport->id], 'method' => 'patch']) !!}

                        @include('datalab.measurement_exports.fields')

                   {!! Form::close() !!}
               </div>
           </div>
       </div>
   </div>
@endsection
