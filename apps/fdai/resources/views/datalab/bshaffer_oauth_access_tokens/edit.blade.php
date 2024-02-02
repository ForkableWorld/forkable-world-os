<?php /** @var App\Models\OAAccessToken $bshafferOauthAccessToken */ ?>
@extends('layouts.admin-lte-app')

@section('content')
   @include('model-header')
   <div class="content">
       @include('adminlte-templates::common.errors')
       <div class="box box-primary">
           <div class="box-body">
               <div class="row">
                   {!! Form::model($bshafferOauthAccessToken, ['route' => ['datalab.oAuthAccessTokens.update', $bshafferOauthAccessToken->access_token], 'method' => 'patch']) !!}

                        @include('datalab.oa_access_tokens.fields')

                   {!! Form::close() !!}
               </div>
           </div>
       </div>
   </div>
@endsection
