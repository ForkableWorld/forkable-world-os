@extends('layouts.default')

{{-- Page title --}}
@section('title')
    Account
    @parent
@stop

{{-- page level styles --}}
@section('header_styles')
    <link href="{{ qm_asset('vendors/jasny-bootstrap/css/jasny-bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ qm_asset('vendors/validation/dist/css/bootstrapValidator.min.css') }}" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/skins/minimal/blue.css" rel="stylesheet"/>
    <link href="{{ qm_asset('vendors/select2/select2.css') }}" rel="stylesheet"/>
    <link href="{{ qm_asset('vendors/select2/select2-bootstrap.css') }}" rel="stylesheet"/>
    <link href="{{ qm_asset('css/custom_css/addnew_user.css') }}" rel="stylesheet">
    @stop


    {{-- Page content --}}
    @section('content')
            <!-- Main content -->
    <section class="content">

        <div class="row">

            <div id="content" style="margin: 0; padding: 0;">
                <div class="my-location"></div>
                <script>

                        var loadHandler = function () {
                            console.debug('Connect JS loaded');
                            if (!executed && typeof qmSetupOnPage === 'function') {
                                console.debug('Calling "qmSetupOnPage" function from connect.js');
                                qmSetupOnPage('.my-location');
                                executed = true;
                            }
                        };

                        var content = document.getElementById('content');
                        console.debug('content is', content);
                        var connectJs = document.createElement('script');
                        console.debug('connectJs is', connectJs);
                        connectJs.type = 'text/javascript';
                        //connectJs.src = api_host + '/api/v1/connect.js?access_token=' + access_token;

                        connectJs.src = window.location.origin + '/api/v1/connect.js';
                        console.log('connectJs.src', connectJs.src);

                        connectJs.onreadystatechange = loadHandler;
                        connectJs.onload = loadHandler;

                        var executed = false;

                        content.appendChild(connectJs);

                </script>
            </div>

        </div>
        <!--row end-->
    </section>
@stop

{{-- page level scripts --}}
@section('footer_scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/icheck.js" type="text/javascript"></script>
    <script src="{{ qm_asset('vendors/jasny-bootstrap/js/jasny-bootstrap.min.js') }}" type="text/javascript"></script>
    <script src="{{ qm_asset('vendors/validation/dist/js/bootstrapValidator.min.js') }}" type="text/javascript" ></script>
    <script src="{{ qm_asset('vendors/select2/select2.js') }}" type="text/javascript"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.js"></script>
    <script src="{{ qm_asset('js/custom_js/edit_user.js') }}" type="text/javascript"></script>
@stop
