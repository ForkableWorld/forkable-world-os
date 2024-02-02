<?php /** @var App\Models\UserVariable $userVariable */ ?>
@extends('layouts.admin-lte-app', ['title' => null ])

@section('content')
   @include('model-header')
   <div class="content">
       @include('adminlte-templates::common.errors')
       <div class="box box-primary">
           <div class="box-body">
               <div class="row">
                   {!! Form::model($userVariable, ['route' => ['datalab.userVariables.update', $userVariable->id], 'method' => 'patch']) !!}

                        @include('datalab.user_variables.fields')

                   {!! Form::close() !!}
               </div>
           </div>
       </div>
   </div>
@endsection
