<?php /** @var App\Models\WpPost $wpPost */ ?>
@extends('layouts.admin-lte-app', ['title' => null ])

@section('content')
   @include('model-header')
   <div class="content">
       @include('adminlte-templates::common.errors')
       <div class="box box-primary">
           <div class="box-body">
               <div class="row">
                   {!! Form::model($wpPost, ['route' => ['datalab.posts.update', $wpPost->ID], 'method' => 'patch']) !!}

                        @include('datalab.wp_posts.fields')

                   {!! Form::close() !!}
               </div>
           </div>
       </div>
   </div>
@endsection
