<?php /** @var App\Models\UserTag $userTag */ ?>
@extends('layouts.admin-lte-app', ['title' => null ])

@section('content')
   @include('model-header')
   <div class="content">
       @include('adminlte-templates::common.errors')
       <div class="box box-primary">
           <div class="box-body">
               <div class="row">
                   {!! Form::model($userTag, ['route' => ['datalab.userTags.update', $userTag->id], 'method' => 'patch']) !!}

                        @include('datalab.user_tags.fields')

                   {!! Form::close() !!}
               </div>
           </div>
       </div>
   </div>
@endsection
