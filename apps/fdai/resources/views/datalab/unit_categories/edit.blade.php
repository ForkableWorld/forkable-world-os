<?php /** @var App\Models\UnitCategory $unitCategory */ ?>
@extends('layouts.admin-lte-app')

@section('content')
   @include('model-header')
   <div class="content">
       @include('adminlte-templates::common.errors')
       <div class="box box-primary">
           <div class="box-body">
               <div class="row">
                   {!! Form::model($unitCategory, ['route' => ['datalab.unitCategories.update', $unitCategory->id], 'method' => 'patch']) !!}

                        @include('datalab.unit_categories.fields')

                   {!! Form::close() !!}
               </div>
           </div>
       </div>
   </div>
@endsection
