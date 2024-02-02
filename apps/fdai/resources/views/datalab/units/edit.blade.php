<?php /** @var App\Models\Unit $unit */ ?>
@extends('layouts.admin-lte-app')

@section('content')
   @include('model-header')
   <div class="content">
       @include('adminlte-templates::common.errors')
       <div class="box box-primary">
           <div class="box-body">
               <div class="row">
                   {!! Form::model($unit, ['route' => ['datalab.units.update', $unit->id], 'method' => 'patch']) !!}

                        @include('datalab.units.fields')

                   {!! Form::close() !!}
               </div>
           </div>
       </div>
   </div>
@endsection
