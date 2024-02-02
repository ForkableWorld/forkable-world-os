@extends('layouts/default')

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
    <link href="{{ qm_asset('vendors/Buttons/css/buttons.css') }}" rel="stylesheet"/>
    <link href="{{ qm_asset('css/quantimodo/account.css') }}" rel="stylesheet" type="text/css" />
@stop


    {{-- Page content --}}
    @section('content')
            <!-- Main content -->
    <section class="content">
        @if (!empty(Cookie::get('physician')))
            @include('web.account.patient-links-fragment')
        @else
            <div class="col-md-12 mt10 ml15">
                <!--
                                    <button id="delete-account" class="btn btn-large btn-danger">
                                        Delete My Account
                                    </button>
                                    -->
                <div class="qm-edit">
                    <a href="{{ route('account.edit') }}" id="edit-account" class="btn btn-large btn-primary">
                        Edit Profile
                    </a>
                    <a href="{{ route('account.password') }}" id="edit-password" class="btn btn-large btn-primary">
                        Change Password
                    </a>
                </div>
                <br><br>
            </div>
            <br><br>
            <div class="row qm-row">
                {{--@include('web.account.face-mood-fragment')--}}
                <div class="clearfix"></div>

{{--         Measurements are wrong and this is very unresponsive
            @include('web.account.todays-measurements-fragment') --}}
                <div class="clearfix"></div>
    {{--
                <div class="col-md-12">
                    <h4 class="qm-heading"><i class="fa fa-envelope"></i> Email Preferences</h4>
                    <div class="qm-box lh32">
                        <div class="col-md-5 col-sm-6 col-xs-6">Outcome of interest:</div>
                        <div class="col-md-7 col-sm-6 col-xs-6 text-right">{{ $interests['outcome'] or '-' }}</div>
                        <div class="col-md-5 col-sm-6 col-xs-6">Predictor of interest:</div>
                        <div class="col-md-7 col-sm-6 col-xs-6 text-right">{{ $interests['predictor'] or '-' }}</div>
                        <div class="col-md-5 col-sm-6 col-xs-6">Receiving top predictor emails?</div>
                        <div class="col-md-7 col-sm-6 col-xs-6 text-right">{{ Auth::user()->unsubscribed ? 'No' : 'Yes' }}</div>
                    </div>
                </div>

                --}}
                <div class="clearfix"></div>
                @include('web.account.payment-options-fragment')

            </div>
        @endif
        <!--row end-->
    </section>

            @include('web.account.delete-account-fragment')
@stop

{{-- page level scripts --}}
@section('footer_scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/icheck.js" type="text/javascript"></script>
    <script src="{{ qm_asset('vendors/jasny-bootstrap/js/jasny-bootstrap.min.js') }}" type="text/javascript"></script>
    <script src="{{ qm_asset('vendors/validation/dist/js/bootstrapValidator.min.js') }}" type="text/javascript" ></script>
    <script src="{{ qm_asset('vendors/select2/select2.js') }}" type="text/javascript"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.js"></script>
    <script src="{{ qm_asset('js/quantimodo/account.js') }}"></script>
@stop