<?php /** @var App\Models\Subscription $subscription */ ?>
@extends('layouts.admin-lte-app', ['title' => null ])

@section('content')
   <section class="content-header" style="min-height: 30px;">
       <h1 class="pull-left">
           <a class="btn btn-primary pull-left"
               href="{{ route('datalab.subscriptions.index') }}">
               <i class="glyphicon glyphicon-chevron-left"></i>
           </a>
            &nbsp; Subscription
        </h1>
       <h1 class="pull-right">
          <a class="btn btn-primary pull-right"
               href="{{ route('datalab.subscriptions.show', [$subscription->id]) }}">
               <i class="glyphicon glyphicon-eye-open" title="Open"></i>
               &nbsp; Preview
          </a>
       </h1>
        @include('single-model-menu-button')
   </section>
   <div class="content">
       @include('adminlte-templates::common.errors')
       <div class="box box-primary">
           <div class="box-body">
               <div class="row">
                   {!! Form::model($subscription, ['route' => ['datalab.subscriptions.update', $subscription->id], 'method' => 'patch']) !!}

                        @include('datalab.subscriptions.fields')

                   {!! Form::close() !!}
               </div>
           </div>
       </div>
   </div>
@endsection
