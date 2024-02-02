<?php /** @var App\Models\Variable $variable */ ?>
@extends('layouts.admin-lte-app', ['title' => $variable->name ])

@section('content')
   @include('model-header')
   <div class="content">
       @include('adminlte-templates::common.errors')
       <div class="box box-primary">
           <div class="box-body">
               <div class="row">
                   {!! Form::model($variable, ['route' => ['datalab.variables.update', $variable->id], 'method' => 'patch']) !!}

                        @include('datalab.variables.fields')

                   {!! Form::close() !!}
               </div>
           </div>
       </div>
   </div>
@endsection
