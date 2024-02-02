<?php /** @var App\Models\VariableCategory $variableCategory */ ?>
@extends('layouts.admin-lte-app', ['title' => $variableCategory->name ])

@section('content')
   @include('model-header')
   <div class="content">
       @include('adminlte-templates::common.errors')
       <div class="box box-primary">
           <div class="box-body">
               <div class="row">
                   {!! Form::model($variableCategory, ['route' => ['datalab.variableCategories.update', $variableCategory->id], 'method' => 'patch']) !!}

                        @include('datalab.variable_categories.fields')

                   {!! Form::close() !!}
               </div>
           </div>
       </div>
   </div>
@endsection
