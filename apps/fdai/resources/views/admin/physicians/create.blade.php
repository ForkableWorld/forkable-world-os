@extends('layouts/default')

{{-- Web site Title --}}
@section('title')
    @lang('physicians/title.create') :: @parent
@stop

@section('header_styles')
    <link href="{{ qm_asset('vendors/jasny-bootstrap/css/jasny-bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ qm_asset('css/quantimodo/app.css') }}" rel="stylesheet" type="text/css" />
@stop

{{-- Content --}}
@section('content')

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-qm-blue ">
                <div class="panel-heading">
                    <h4 class="panel-title">
                        @lang('physicians/title.create') {{patientAlias()}} Authorization URL
                    </h4>
                </div>
                <div class="panel-body">
                    <p class="lead small">You'll receive a shareable authorization URL you can give to your {{strtolower(patientAlias())}}s.  Once a patient authorizes you to access their data, you'll be able to export it as a spreadsheet for analysis.</p>

                    @include('errors.errors')
                    <form class="form-horizontal" role="form" method="post" enctype="multipart/form-data" action="">
                        <!-- CSRF Token -->
                        <input type="hidden" name="_token" value="{{ csrf_token() }}" />

                        <div class="form-group">
                            <label for="app_display_name" class="col-md-2 control-label">
                                Display Name*
                            </label>
                            <div class="col-md-5">
                                <input type="text" maxlength="100" required="required" id="app_display_name" name="app_display_name" class="form-control" placeholder="Enter your name..." value="{!! Auth::user()->display_name !!}">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-offset-2 col-md-4">
                                <a class="btn btn-qm-orange" href="{{ route('physicians') }}">
                                    @lang('button.cancel')
                                </a>
                                <button type="submit" class="btn btn-qm-green">
                                    @lang('button.save')
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- row-->
</section>
@stop

@section('footer_scripts')
    <script src="{{ qm_asset('vendors/jasny-bootstrap/js/jasny-bootstrap.min.js') }}" type="text/javascript"></script>
    <script src="{{ qm_asset('vendors/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js') }}" type="text/javascript"></script>
    <script src="{{ qm_asset('js/quantimodo/form_editors.js') }}" type="text/javascript"></script>
    <script src="{{ qm_asset('js/quantimodo/applications_dashboard.js') }}" type="text/javascript"></script>
@stop
