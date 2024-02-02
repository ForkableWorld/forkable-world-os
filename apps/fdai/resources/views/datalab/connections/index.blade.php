@extends('layouts.admin-lte-app', ['title' => 'Connections' ])
@section('content')
    @include('index-header')
   <div class="content">
        <div class="clearfix"></div>
        @include('flash::message')
        <div class="clearfix"></div>
        @component('async-widget', \App\Widgets\NeverImportedChartWidget::getWidgetParams())@endcomponent
        <div class="box box-primary">
            <div class="box-body">
                    @include('table')
            </div>
        </div>
        <div class="text-center">
        </div>
    </div>
@endsection