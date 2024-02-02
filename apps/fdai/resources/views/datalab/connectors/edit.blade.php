<?php /** @var App\Models\Connector $connector */ ?>
@extends('layouts.admin-lte-app', ['title' => $connector->display_name ])

@section('content')
   @include('model-header')
   <div class="content">
       @include('adminlte-templates::common.errors')
       <div class="box box-primary">
           <div class="box-body">
               <div class="row">
                   {!! Form::model($connector, ['route' => ['datalab.connectors.update', $connector->id], 'method' => 'patch']) !!}

                        @include('datalab.connectors.fields')

                   {!! Form::close() !!}
               </div>
           </div>
       </div>
   </div>
@endsection
