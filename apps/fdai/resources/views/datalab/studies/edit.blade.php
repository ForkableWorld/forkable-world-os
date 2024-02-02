<?php /** @var App\Models\Study $study */ ?>
@extends('layouts.admin-lte-app', ['title' => null ])

@section('content')
   @include('model-header')
   <div class="content">
       @include('adminlte-templates::common.errors')
       <div class="box box-primary">
           <div class="box-body">
               <div class="row">
                   {!! Form::model($study, ['route' => ['datalab.studies.update', $study->id], 'method' => 'patch']) !!}

                        @include('datalab.studies.fields')

                   {!! Form::close() !!}
               </div>
           </div>
       </div>
   </div>
@endsection
