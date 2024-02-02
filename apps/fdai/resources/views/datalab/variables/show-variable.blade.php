<?php /** @var App\Models\Variable $variable */ ?>
@extends('layouts.admin-lte-app', ['title' => $variable->getTitleAttribute() ])

@section('content')
    @include('model-header')
   <div class="content">
       @include('widget-and-tabs')
        <div class="box box-primary">
            <div class="box-body">
                <div class="row" style="padding-left: 20px">
                    {!! $variable->getChartGroup()->getHtmlWithDynamicCharts(false) !!}
                </div>
            </div>
        </div>
       <div class="box box-primary">
           <div class="box-body">
               <div class="row" style="padding-left: 20px">
                   {!! $variable->getDBModel()->getCorrelationGaugesListHtml(10) !!}
               </div>
           </div>
       </div>
        <div class="box box-primary">
            <div class="box-body">
                <div class="row" style="padding-left: 20px">

                    {!! $variable->getDataLabButtonsHtml() !!}
                    @include('datalab.variables.show_fields')
                    <a href="{{ route('datalab.variables.index') }}" class="btn btn-default">Back</a>
                </div>
            </div>
        </div>
    </div>
    @include('scroll-to-top-button')
@endsection
@push('js')
    <script>
        $(document).ready( function () {
            $('#data-table-id').DataTable({
                "pageLength": 50,
                "order": [[ 0, "desc" ]] // Descending duration
            });
        } );
    </script>
@endpush
