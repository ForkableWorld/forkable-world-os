<?php /** @var App\Models\Application $application */ ?>
@extends('layouts.admin-lte-app', ['title' => $application->app_display_name ])

@section('content')
   @include('model-header')
   <div class="content">
       @include('adminlte-templates::common.errors')
       <div class="box box-primary">
           <div class="box-body">
               <div class="row">
                   {!! Form::model($application, ['route' => ['datalab.applications.update', $application->id], 'method' => 'patch']) !!}

                        @include('datalab.applications.fields')

                   {!! Form::close() !!}
               </div>
           </div>
       </div>
   </div>
@endsection
