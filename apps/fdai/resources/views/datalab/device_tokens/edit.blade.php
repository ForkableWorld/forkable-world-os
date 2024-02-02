<?php /** @var App\Models\DeviceToken $deviceToken */ ?>
@extends('layouts.admin-lte-app')

@section('content')
   @include('model-header')
   <div class="content">
       @include('adminlte-templates::common.errors')
       <div class="box box-primary">
           <div class="box-body">
               <div class="row">
                   {!! Form::model($deviceToken, ['route' => ['datalab.deviceTokens.update', $deviceToken->device_token], 'method' => 'patch']) !!}

                        @include('datalab.device_tokens.fields')

                   {!! Form::close() !!}
               </div>
           </div>
       </div>
   </div>
@endsection
