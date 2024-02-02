<?php /** @var App\Models\TrackingReminderNotification $trackingReminderNotification */ ?>
@extends('layouts.admin-lte-app')

@section('content')
   @include('model-header')
   <div class="content">
       @include('adminlte-templates::common.errors')
       <div class="box box-primary">
           <div class="box-body">
               <div class="row">
                   {!! Form::model($trackingReminderNotification, ['route' => ['datalab.trackingReminderNotifications.update', $trackingReminderNotification->id], 'method' => 'patch']) !!}

                        @include('datalab.tracking_reminder_notifications.fields')

                   {!! Form::close() !!}
               </div>
           </div>
       </div>
   </div>
@endsection
