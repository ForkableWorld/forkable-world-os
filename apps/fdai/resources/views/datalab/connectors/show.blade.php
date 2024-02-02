<?php /** @var App\Models\Connector $connector */ 
$model = $connector ?? $model;
$connector = $connector ?? $model;
?>
@extends('layouts.admin-lte-app', ['title' => $connector->display_name ])

@section('content')
    <section class="content-header" style="min-height: 30px;">
       <h1 class="pull-left">
           <a class="btn btn-primary pull-left"
               href="{{ route('datalab.connectors.index') }}">
               <i class="glyphicon glyphicon-chevron-left"></i>
           </a>
            &nbsp; Connector
        </h1>
        <h1 class="pull-right">
            <a class="btn btn-primary pull-right"
               href="{{ route('datalab.measurements.index', ['connector_id' => $connector->id]) }}">
                <i class="fa fa-list" title="Measurements"></i>
                &nbsp; Measurements
            </a>
            <a class="btn btn-primary pull-right"
               href="{{ route('datalab.connections.index', ['connector_id' => $connector->id]) }}">
                <i class="fa fa-connectdevelop" title="Connections"></i>
                &nbsp; Connections
            </a>
            @component('single-model-menu-button', [
                'links' => [
                    [
                        'title' => 'Imports',
                        'icon' => \App\UI\FontAwesome::CLOUD_DOWNLOAD_ALT_SOLID,
                        'url' => route('datalab.connectorImports.index', ['connector_id' => $connector->id]),
                    ],
                    [
                        'title' => 'Connections',
                        'icon' => \App\UI\FontAwesome::CONNECTDEVELOP,
                        'url' => route('datalab.connections.index', ['connector_id' => $connector->id]),
                    ],
                    [
                        'title' => 'Imports',
                        'icon' => \App\UI\FontAwesome::CLOUD_DOWNLOAD_ALT_SOLID,
                        'url' => route('datalab.connectorImports.index', ['connector_id' => $connector->id]),
                    ],
                ],
                'model' => $connector
            ])@endcomponent
        </h1>
         @include('single-model-menu-button')
   </section>
   <div class="content">
{{--        @include('connector')--}}
        @isadmin
            @component('async-widget',
                \App\Widgets\MeasurementsForConnectorOverTimeChartWidget::getWidgetParamsByModel($connector))
             @endcomponent
        @endisadmin
        <div class="box box-primary">
            <div class="box-body">
                <div class="row" style="padding-left: 20px">
                    @include('datalab.connectors.show_fields')
                    <a href="{{ route('datalab.connectors.index') }}" class="btn btn-default">Back</a>
                </div>
            </div>
        </div>
    </div>
@endsection
