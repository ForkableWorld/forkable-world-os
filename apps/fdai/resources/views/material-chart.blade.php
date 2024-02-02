<?php /** @var \App\Charts\QMHighcharts\BaseHighstock $chart */ ?>
@extends('layouts.material-app', ['title' => $chart->getTitleAttribute(), 'activePage' => \App\Http\Controllers\ChartsController::CHARTS_PATH, ])
@section('content')
    <div class="content">
        <div class="clearfix"></div>
        @include('flash::message')
        <div class="row">
            <div class="col-md-12">
                {!!  $chart->inlineNoHeading() !!}
            </div>
        </div>
        <div class="text-center"></div>
    </div>
@endsection
