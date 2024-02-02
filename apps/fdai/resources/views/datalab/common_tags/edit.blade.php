<?php /** @var App\Models\CommonTag $commonTag */ ?>
@extends('layouts.admin-lte-app', ['title' => null ])

@section('content')
   @include('model-header')
   <div class="content">
       @include('adminlte-templates::common.errors')
       <div class="box box-primary">
           <div class="box-body">
               <div class="row">
                   {!! Form::model($commonTag, ['route' => ['datalab.commonTags.update', $commonTag->id], 'method' => 'patch']) !!}

                        @include('datalab.common_tags.fields')

                   {!! Form::close() !!}
               </div>
           </div>
       </div>
   </div>
@endsection
