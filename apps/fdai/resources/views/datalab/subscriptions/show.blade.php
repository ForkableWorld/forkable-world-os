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
                href="{{ route('datalab.subscriptions.edit', [$subscription->id]) }}">
                <i class="glyphicon glyphicon-edit" title="Edit"></i>
                &nbsp; Edit
           </a>
        </h1>
         @include('single-model-menu-button')
   </section>
   <div class="content">
        <div class="box box-primary">
            <div class="box-body">
                <div class="row" style="padding-left: 20px">
                    @include('datalab.subscriptions.show_fields')
                    <a href="{{ route('datalab.subscriptions.index') }}" class="btn btn-default">Back</a>
                </div>
            </div>
        </div>
    </div>
@endsection
