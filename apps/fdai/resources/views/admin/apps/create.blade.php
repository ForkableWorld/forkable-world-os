@extends('layouts/default')

{{-- Web site Title --}}
@section('title')
    @lang('apps/title.create') :: @parent
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
            <div class="panel panel-primary ">
                <div class="panel-heading">
                    <h4 class="panel-title">
                        @lang('apps/title.create')
                        {{--<button type="submit" class="btn btn-success pull-right">@lang('button.save')</button> TODO: Fix me--}}
                    </h4>
                </div>
                <div class="panel-body">
                    @include('errors.errors')
                    <form class="form-horizontal" role="form" method="post" enctype="multipart/form-data" action="">
                        <!-- CSRF Token -->
                        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                        @include('components.inputs.name-input')
                        @include('components.inputs.subdomain-input')
                        @include('components.inputs.description-input')
                        @include('components.inputs.homepage-input')
                        {{--@include('components.icon-uploader')--}}
                        {{--@include('components.text-logo-uploader')--}}
                        {{--@include('components.splash-screen-uploader')--}}
                        @include('components.inputs.redirect-uri-input')
                        <div class="form-group">
                            <div class="col-md-offset-2 col-md-4">
                                <a class="btn btn-danger" href="{{ route('apps') }}">@lang('button.cancel')</a>
                                <button type="submit" class="btn btn-success">@lang('button.save')</button>
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
