<?php /** @var App\Models\TrackingReminder $trackingReminder */ ?>
@extends('layouts.admin-lte-app')

@section('content')
   @include('model-header')
   <div class="content">
       @include('adminlte-templates::common.errors')
       <div class="box box-primary">
           <div class="box-body">
               <div class="row">
                   {!! Form::model($trackingReminder, ['route' => ['datalab.trackingReminders.update', $trackingReminder->id], 'method' => 'patch']) !!}

                        @include('datalab.tracking_reminders.fields')

                   {!! Form::close() !!}
               </div>
           </div>
       </div>
   </div>
@endsection
