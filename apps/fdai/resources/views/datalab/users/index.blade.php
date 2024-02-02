@extends('layouts.admin-lte-app', ['title' => 'Users' ])
@section('content')
    @include('index-header')
   <div class="content">
        <div class="clearfix"></div>
        @include('flash::message')
        <div class="clearfix"></div>
        @isadmin
            @empty( $_GET )
               @component('async-widget',
                    \App\Widgets\LastLoginChartWidget::getWidgetParams())
                @endcomponent
            @endempty
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