@extends('layouts/default')
{{-- Web site Title --}}
@section('title')
@lang('studies/title.management')
@parent
@stop
@section('header_styles')
    <link href="{{ qm_asset('css/quantimodo/app.css') }}" rel="stylesheet" type="text/css" />
@stop
{{-- Content --}}
@section('content')
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-primary ">
                <div class="panel-heading clearfix" data-container="body" data-toggle="popover" data-placement="bottom" data-trigger="hover"
                     data-content="By creating a study, you'll receive a shareable authorization URL you can give to potential study participants.  Once a participant authorizes you to access their data, you'll be able to export it as a spreadsheet for analysis.">
                    <h4 class="panel-title pull-left"> <i class="fa fa-university"></i> Studies </h4>
                    <div class="pull-right"> <a href="{{ route('create/study') }}" class="btn btn-sm btn-default"><span class="glyphicon glyphicon-plus"></span> @lang('button.create')</a> </div>
                </div>
                <br />
                <div class="panel-body">
                    @if ( count($applications) )
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>@lang('studies/table.name')</th>
                                    <th>@lang('studies/table.actions')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ( $applications as $application )
                                <tr class="status">
                                    <td> <a href="{{ route('update/study', $application->id) }}"> {!! $application->app_display_name !!} </a> </td>
                                    <td>
                                        <a href="{{ route('update/study', $application->id) }}"> <i class="fa fa-fw fa-pencil text-warning" data-toggle="tooltip" data-placement="top" title="Edit study details"></i> </a>
                                        @if(Auth::user()->ID == $application->user_id)
                                            <a href="{{ route('confirm-delete/study', $application->id) }}" data-toggle="modal" data-target="#delete_confirm">
                                                <i class="fa fa-fw fa-times text-danger" data-toggle="tooltip" data-placement="top" title="Delete this study"></i>
                                            </a>
                                        @endif
                                        <div class="btn-group export-measurements" data-app-id="{{ $application->id }}" data-client-id="{{ $application->client_id }}" data-toggle="tooltip"
                                             data-placement="top" title="Export all participant data">
                                            <i class="fa fa-fw fa-download text-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></i>
                                            <ul class="dropdown-menu">
                                                <li data-output="csv"><a href="#">CSV</a></li>
                                                <li data-output="pdf"><a href="#">PDF</a></li>
                                                <li data-output="xls"><a href="#">XLS</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <h5 class="text-center">Please create your first study by clicking
                            <a href="{{ route('create/study') }}" class="btn btn-sm btn-default"><span class="glyphicon glyphicon-plus"></span> @lang('button.create')</a>
                        </h5>
                    @endif
                </div>
            </div>
        </div>
    </div>    <!-- row-->
</section>
@stop
@section('footer_scripts')
    <script src="{{ qm_asset('js/quantimodo/applications_dashboard.js') }}" type="text/javascript"></script>
@stop
