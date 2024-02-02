@extends('layouts/default')
{{-- Page title --}}
@section('title')
    Account Management
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
    <?php $user = Auth::user()?>
            <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                @include('errors.errors')
                @if (empty(Cookie::get('physician')))
                    <h4 class="panel-heading3"><i class="fa fa-user"></i> Edit Profile</h4>
                    <form class="form-horizontal" role="form" method="post" action="">
                        <!-- CSRF Token -->
                        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                        <div class="form-group">
                            <label for="display_name" class="col-sm-2 control-label"> Avatar </label>
                            <div class="col-sm-2">
                                <a target="_blank" href="https://en.gravatar.com/emails/">
                                    <img class="img-circle"
                                         src="{{ $user->avatar_image }}"
                                         alt="avatar image">
                                </a>
                                {{--<input type="text" id="display_name" name="display_name" class="form-control" placeholder="Your Name" value="{!! Request::old('display_name', $user->display_name) !!}">--}}
                            </div>
                            <div class="col-sm-5">
                                <a href="{{ route('account.password') }}" id="edit-password"
                                   class="btn btn-large btn-primary">
                                    Change Password
                                </a>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="display_name" class="col-sm-2 control-label"> Name </label>
                            <div class="col-sm-5"> <input type="text" id="display_name" name="display_name" class="form-control" placeholder="Your Name" value="{!! Request::old('display_name', $user->display_name) !!}"> </div>
                        </div>
                        <div class="form-group">
                            <label for="user_login" class="col-sm-2 control-label"> Username </label>
                            <div class="col-sm-5"> <input type="text" id="user_login" name="user_login" class="form-control" placeholder="Username" value="{!! Request::old('user_login', $user->user_login) !!}"> </div>
                        </div>
                        <div class="form-group">
                            <label for="email" class="col-sm-2 control-label"> Email </label>
                            <div class="col-sm-5"> <input type="text" id="email" name="email" class="form-control" placeholder="Email" value="{!! Request::old('email', $user->email) !!}"> </div>
                        </div>
                        <br>
    {{--
                        <div class="col-sm-offset-2">You will receive weekly emails about outcomes and predictors when you fill them.</div>
                        <div class="form-group mt10">
                            <label for="outcome" class="col-sm-2 control-label"> Outcome of interest </label>
                            <div class="col-sm-3"> <input type="text" id="outcome" class="form-control variable-search" placeholder="Type to search..." value=""> </div>
                            <div id="outcome-text" class="col-sm-5 form-control-static">{{ $interests['outcome'] or '' }}</div>
                            <input type="hidden" id="outcome-id" name="outcome_id">
                        </div>
                        <div class="form-group">
                            <label for="predictor" class="col-sm-2 control-label"> Predictor of interest </label>
                            <div class="col-sm-3"> <input type="text" id="predictor" class="form-control variable-search" placeholder="Type to search..." value=""> </div>
                            <div id="predictor-text" class="col-sm-5 form-control-static">{{ $interests['predictor'] or '' }}</div>
                            <input type="hidden" id="predictor-id" name="predictor_id">
                        </div>
                        --}}

                        <div class="form-group status">
                            <label for="unsubscribed" class="col-sm-2 control-label"> Unsubscribe from emails </label>
                            <div class="col-sm-5"> <input id="unsubscribed" type="checkbox" name="unsubscribed" value="1" {{ $user->unsubscribed ? 'checked="checked"' : '' }}> </div>
                        </div>
                        <div class="form-group"> <div class="col-sm-offset-2 col-sm-4"> <button type="submit" class="btn btn-success"> Save Changes </button> </div> </div>
                    </form>
                    <label for="access_token" class="col-sm-2 control-label"> Access Token </label>
                    <span id="access_token">{{ $accessTokenString }}</span>
                    <h4 class="panel-heading3"><i class="fa fa-upload"></i> Upload MedHelper Export</h4>
                    <form method="post" action="{{ route("account.upload.spreadsheet") }}" class="form-horizontal" enctype="multipart/form-data">
                        <input type="hidden" name="connectorName" value="medhelper">
                        <div class="form-group">
                            <label for="file" class="col-sm-2 control-label"> File </label>
                            <div class="col-sm-6"> <input type="file" name="file" required="required"> <p class="help-block">Upload your spreadsheet here to import measurements from MedHelper</p> </div>
                        </div>
                        <div class="form-group"> <div class="col-sm-offset-2 col-sm-4"> <button type="submit" class="btn btn-success"> Upload </button> </div> </div>
                    </form>
                @else
                    <h5 class="text-center">
                        <button href="{{ route('account.back') }}" type="button"  class="btn btn-sm btn-default">
                            <a href="{{ route('account.back') }}">Acting as {{ Auth::user()->display_name }}.  Click here to switch back to your account.</a>
                        </button>
                    </h5>
                    @include('web.account.patient-links-fragment')
                @endif
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
    <script src="{{ qm_asset('js/quantimodo/account.edit.js') }}" type="text/javascript"></script>
@stop
