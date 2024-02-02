<?php /** @var App\Models\OAClient $bshafferOauthClient */ ?>
@extends('layouts.admin-lte-app')

@section('content')
   @include('model-header')
   <div class="content">
       @include('adminlte-templates::common.errors')
       <div class="box box-primary">
           <div class="box-body">
               <div class="row">
                   {!! Form::model($bshafferOauthClient, ['route' => ['datalab.oAuthClients.update', $bshafferOauthClient->client_id], 'method' => 'patch']) !!}

                        @include('datalab.oa_clients.fields')

                   {!! Form::close() !!}
               </div>
           </div>
       </div>
   </div>
@endsection
