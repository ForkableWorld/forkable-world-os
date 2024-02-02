<?php /** @var App\Models\SentEmail $sentEmail */ ?>
@extends('layouts.admin-lte-app', ['title' => null ])

@section('content')
   @include('model-header')
   <div class="content">
       @include('adminlte-templates::common.errors')
       <div class="box box-primary">
           <div class="box-body">
               <div class="row">
                   {!! Form::model($sentEmail, ['route' => ['datalab.sentEmails.update', $sentEmail->id], 'method' => 'patch']) !!}

                        @include('datalab.sent_emails.fields')

                   {!! Form::close() !!}
               </div>
           </div>
       </div>
   </div>
@endsection
