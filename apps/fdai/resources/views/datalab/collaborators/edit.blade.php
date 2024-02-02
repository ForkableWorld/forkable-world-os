<?php /** @var App\Models\Collaborator $collaborator */ ?>
@extends('layouts.admin-lte-app', ['title' => null ])

@section('content')
   @include('model-header')
   <div class="content">
       @include('adminlte-templates::common.errors')
       <div class="box box-primary">
           <div class="box-body">
               <div class="row">
                   {!! Form::model($collaborator, ['route' => ['datalab.collaborators.update', $collaborator->id], 'method' => 'patch']) !!}

                        @include('datalab.collaborators.fields')

                   {!! Form::close() !!}
               </div>
           </div>
       </div>
   </div>
@endsection
