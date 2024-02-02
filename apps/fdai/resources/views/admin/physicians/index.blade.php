@extends('layouts/default')
{{-- Web site Title --}}
@section('title')
@lang('physicians/title.management')
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
                <div class="panel-heading clearfix"
                     {{--data-container="body"--}}
                     {{--data-toggle="popover"--}}
                     {{--data-placement="bottom"--}}
                     {{--data-content="By creating a physician, you'll receive a shareable authorization URL you can give to potential physician participants.  Once a participant authorizes you to access their data, you'll be able to export it as a spreadsheet for analysis."--}}
                     {{--data-trigger="hover"--}}>
                    <h4 class="panel-title pull-left"> <i class="fa fa-stethoscope"></i> {{physicianAlias()}} </h4>
                </div>
                <br />
                <div class="panel-body">
                    @if (isset($applications) && $applications)
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>@lang('physicians/table.name')</th>
                                    <th>@lang('physicians/table.status')</th>
                                    <th>@lang('physicians/table.created_at')</th>
                                    <th>@lang('physicians/table.actions')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($applications as $application)
                                <tr class="status">
                                    <td> <a href="{{ route('update/physician', $application->id) }}"> {!! $application->app_display_name !!} </a> </td>
                                    <td class="{!! $application->status !!}">{!! $application->status !!}</td>
                                    <td>{!! $application->created_at->diffForHumans() !!}</td>
                                    <td>
                                        <a href="{{ route('update/physician', $application->id) }}">
                                            <i class="fa fa-fw fa-pencil text-warning" data-toggle="tooltip" data-placement="top" title="Edit physician details"></i>
                                        </a>
                                        @if(Auth::user()->ID == $application->user_id)
                                            <a href="{{ route('confirm-delete/physician', $application->id) }}" data-toggle="modal" data-target="#delete_confirm">
                                                <i class="fa fa-fw fa-times text-danger" data-toggle="tooltip" data-placement="top" title="Delete this physician"></i>
                                            </a>
                                        @endif
                                        <div class="btn-group export-measurements" data-app-id="{{ $application->id }}" data-client-id="{{ $application->client_id }}" data-toggle="tooltip" data-placement="top" title="Export all participant data">
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
                    @elseif (empty(Cookie::get('physician')))
                        Create a shareable authorization URL you can give to potential physician participants.  Once a participant authorizes you to access their data, you'll be able to:
                        <ul type="1">
                            <li>- See {{strtolower(patientAlias())}} analytics such as the strongest predictors of their symptoms</li>
                            <li>- Add treatment and symptom rating reminders reminders</li>
                            <li>- Add new symptom ratings, treatments, and other measurements for them</li>
                            <li>- Import their digital health data from other apps and devices</li>
                            <li>- Review their past symptoms, treatments, vitals, and other measurements</li>
                            <li>- Export their data as a spreadsheet or PDF</li>
                        </ul>
                        <form class="form-horizontal" role="form" method="post" enctype="multipart/form-data" action="{{ route('create/physician') }}">
                            <!-- CSRF Token -->
                            <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                            <input type="hidden" maxlength="150" required="required" id="name" name="name" class="form-control" placeholder="Enter your name..." value="{!! Auth::user()->display_name !!}">
                        <h5 class="text-center"><button type="submit" class="btn btn-sm btn-default"><span class="glyphicon glyphicon-plus"></span> @lang('button.create') Your {{patientAlias()}} Authorization URL</button></h5>
                        </form>
                    @else
                        <h5 class="text-center">
                            <button href="{{ route('account.back') }}" type="button"  class="btn btn-sm btn-default"></span>
                                <a href="{{ route('account.back') }}">Acting as {{ Auth::user()->display_name }}.  Click here to switch back to your account.</a>
                            </button>
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
