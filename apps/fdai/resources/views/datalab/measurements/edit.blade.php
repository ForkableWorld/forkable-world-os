<?php /** @var App\Models\Measurement $measurement */ ?>
@extends('layouts.admin-lte-app', ['title' => null ])

@section('content')
   @include('model-header')
   <div class="content">
       @include('adminlte-templates::common.errors')
       <div class="box box-primary">
           <div class="box-body">
               <div class="row">
                   {!! Form::model($measurement, ['route' => ['datalab.measurements.update', $measurement->id], 'method' => 'patch']) !!}

                        @include('datalab.measurements.fields')

                   {!! Form::close() !!}
               </div>
           </div>
       </div>
   </div>
@endsection
