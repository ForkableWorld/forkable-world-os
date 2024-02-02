@extends('layouts/default')
@section('title')
    Integration Guide
    @parent
@stop
@section('header_styles')
    <link href="{{ qm_asset('vendors/twitter-bootstrap-wizard/prettify.css') }}" rel="stylesheet">
    <link href="{{ qm_asset('vendors/select2/select2.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ qm_asset('vendors/select2/select2-bootstrap.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ qm_asset('css/quantimodo/plans.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ qm_asset('vendors/jasny-bootstrap/css/jasny-bootstrap.min.css') }}" rel="stylesheet"/>
    <link href="{{ qm_asset('css/quantimodo/app.css') }}" rel="stylesheet" type="text/css"/>
@stop
@section('content')
    <section class="content">
        <div class="row">
            <div class="col-lg-12">
                <div class="panel panel-primary ">
                    <div class="panel-heading"> <h4 class="panel-title"> Integration Guide </h4> </div>
                    <div class="panel-body">
                        @include('errors.errors')
                        <div>
                            <ul class="nav nav-tabs" role="tablist">
                                <li role="presentation" class="active"><a href="#authentication-web" aria-controls="authentication" role="tab" data-toggle="tab">1. Web Authentication</a></li>
                                <li role="presentation"><a href="#authentication-wordpress" aria-controls="authentication" role="tab" data-toggle="tab">1. WordPress Authentication</a></li>
                                <li role="presentation"><a href="#authentication-cordova" aria-controls="authentication" role="tab" data-toggle="tab">1. Cordova Authentication</a></li>
                                <li role="presentation"><a href="#credentials" aria-controls="credentials" role="tab" data-toggle="tab">2. Get Credentials</a></li>
                                <li role="presentation"><a href="#data-retrieval" aria-controls="data-retrieval" role="tab" data-toggle="tab">3. Retrieve Data</a></li>
                            </ul>
                            <div class="tab-content">
                                <div id="authentication-web" role="tabpanel" class="tab-pane fade in active"> @include('docs.components.integration-authentication-web') </div>
                                <div id="authentication-wordpress" role="tabpanel" class="tab-pane fade in"> @include('docs.components.integration-authentication-wordpress') </div>
                                <div id="authentication-cordova" role="tabpanel" class="tab-pane fade in"> @include('docs.components.integration-authentication-cordova') </div>
                                <div id="credentials" role="tabpanel" class="tab-pane fade in"> @include('docs.components.integration-credentials') </div>
                                <div id="data-retrieval" role="tabpanel" class="tab-pane fade in"> @include('docs.components.integration-data-retrieval') </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@stop
@section('footer_scripts')
    <script src="{{ qm_asset('vendors/jasny-bootstrap/js/jasny-bootstrap.min.js') }}" type="text/javascript"></script>
    <script src="{{ qm_asset('vendors/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js') }}" type="text/javascript"></script>
    <script src="{{ qm_asset('js/quantimodo/form_editors.js') }}" type="text/javascript"></script>
    <script src="{{ qm_asset('js/quantimodo/applications_dashboard.js') }}" type="text/javascript"></script>
    <script src="https://checkout.stripe.com/checkout.js"></script>
    <script src="{{ qm_asset('vendors/twitter-bootstrap-wizard/jquery.bootstrap.wizard.js') }}"></script>
    <script src="{{ qm_asset('vendors/twitter-bootstrap-wizard/prettify.js') }}"></script>
    <script src="{{ qm_asset('vendors/validation/jquery.validate.min.js') }}"></script>
    <script src="{{ qm_asset('vendors/select2/select2.js') }}" type="text/javascript"></script>
    {!! qm_integration_loader_and_options() !!}
@stop