<?php /** @var App\Models\ConnectorImport $connectorImport */ ?>
@extends('layouts.admin-lte-app', ['title' => null ])

@section('content')
   @include('model-header')
   <div class="content">
       @include('adminlte-templates::common.errors')
       <div class="box box-primary">
           <div class="box-body">
               <div class="row">
                   {!! Form::model($connectorImport, ['route' => ['datalab.connectorImports.update', $connectorImport->id], 'method' => 'patch']) !!}

                        @include('datalab.connector_imports.fields')

                   {!! Form::close() !!}
               </div>
           </div>
       </div>
   </div>
@endsection
