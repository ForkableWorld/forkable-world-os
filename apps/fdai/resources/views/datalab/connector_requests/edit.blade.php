<?php /** @var App\Models\ConnectorRequest $connectorRequest */ ?>
@extends('layouts.admin-lte-app')

@section('content')
   @include('model-header')
   <div class="content">
       @include('adminlte-templates::common.errors')
       <div class="box box-primary">
           <div class="box-body">
               <div class="row">
                   {!! Form::model($connectorRequest, ['route' => ['datalab.connectorRequests.update', $connectorRequest->id], 'method' => 'patch']) !!}

                        @include('datalab.connector_requests.fields')

                   {!! Form::close() !!}
               </div>
           </div>
       </div>
   </div>
@endsection
