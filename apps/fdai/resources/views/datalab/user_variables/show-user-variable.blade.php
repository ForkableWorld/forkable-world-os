<?php /** @var App\Models\UserVariable $userVariable */ ?>
@extends('layouts.admin-lte-app', ['title' => $userVariable->getTitleAttribute() ])
@section('content')
    @include('model-header')
   <div class="content">
       @include('widget-and-tabs')
       <div class="box box-primary">
           <div class="box-body">
               <div class="row" style="padding-left: 20px;">
                   {!! $userVariable->getChartGroup()->getHtmlWithDynamicCharts(false) !!}
               </div>
           </div>
       </div>
    </div>
@endsection
