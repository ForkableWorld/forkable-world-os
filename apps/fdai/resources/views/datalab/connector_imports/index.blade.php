@extends('layouts.admin-lte-app', ['title' => 'Connector Imports' ])
@section('content')
    @include('index-header')
   <div class="content">
        <div class="clearfix"></div>
        @include('flash::message')
        <div class="clearfix"></div>
        @isadmin
            @component('async-widget', \App\Widgets\CompletedImportsChartWidget::getWidgetParams())@endcomponent
        @endisadmin
        <div class="box box-primary">
            <div class="box-body">
                @include('table')
            </div>
        </div>
        <div class="text-center">
        </div>
    </div>
@endsection