@extends('layouts.admin-lte-app', ['title' => "Posts" ])
@section('content')
    @include('index-header')
   <div class="content">
        <div class="clearfix"></div>
        @include('flash::message')
        <div class="clearfix"></div>
       {!! \App\Models\WpPost::getCountPercentOfAllRecordsBox(\App\Models\WpPost::FIELD_POST_NAME.' like "%user%"', "User Posts", "user") !!}
       @component('async-widget', \App\Widgets\PostsUpdatedChartWidget::getWidgetParams())@endcomponent
        <div class="box box-primary">
            <div class="box-body">
                @include('table')
            </div>
        </div>
    </div>
@endsection
