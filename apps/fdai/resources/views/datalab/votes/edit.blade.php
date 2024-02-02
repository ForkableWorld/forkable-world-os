<?php /** @var App\Models\Vote $vote */ ?>
@extends('layouts.admin-lte-app', ['title' => null ])

@section('content')
   @include('model-header')
   <div class="content">
       @include('adminlte-templates::common.errors')
       <div class="box box-primary">
           <div class="box-body">
               <div class="row">
                   {!! Form::model($vote, ['route' => ['datalab.votes.update', $vote->id], 'method' => 'patch']) !!}

                        @include('datalab.votes.fields')

                   {!! Form::close() !!}
               </div>
           </div>
       </div>
   </div>
@endsection
