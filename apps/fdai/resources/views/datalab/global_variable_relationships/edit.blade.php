<?php /** @var App\Models\GlobalVariableRelationship $aggregateCorrelation */ ?>
@extends('layouts.admin-lte-app', ['title' => $aggregateCorrelation->getTitleAttribute() ])

@section('content')
   @include('model-header')
   <div class="content">
       @include('adminlte-templates::common.errors')
       <div class="box box-primary">
           <div class="box-body">
               <div class="row">
                   {!! Form::model($aggregateCorrelation, ['route' => ['datalab.aggregateCorrelations.update', $aggregateCorrelation->id], 'method' => 'patch']) !!}

                        @include('datalab.global_variable_relationships.fields')

                   {!! Form::close() !!}
               </div>
           </div>
       </div>
   </div>
@endsection
