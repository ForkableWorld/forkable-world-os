<?php /** @var App\Models\Connection $connection */ ?>
@extends('layouts.admin-lte-app', ['title' => null ])

@section('content')
   @include('model-header')
   <div class="content">
       @include('adminlte-templates::common.errors')
       <div class="box box-primary">
           <div class="box-body">
               <div class="row">
                   {!! Form::model($connection, ['route' => ['datalab.connections.update', $connection->id], 'method' => 'patch']) !!}

                        @include('datalab.connections.fields')

                   {!! Form::close() !!}
               </div>
           </div>
       </div>
   </div>
@endsection
