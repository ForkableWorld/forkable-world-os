@extends('layouts.auth')

@section('title')
    <title>Register</title>
@endsection

@section('content')
    <div class="row " id="form-login">
        <div class="panel-header">
{{--            <div class="logo">
                <img src="https://static.quantimo.do/img/QM-LOGO-black-300x85.png" alt="logo" height="59px" />
            </div>--}}
            @if (showLogo())
                <img style="display: block; margin: 0 auto; max-width: 25%;" src="{{ text_logo() }}" alt="logo image">
            @endif
            <h1 class="text-center">Sign Up</h1>
            <h4 class="text-center">Already have an account?</h4>
            <h4 class="text-center">
                <a id="login-page-link"
                    style="color: white !important; text-decoration: underline;"
                    href="{!! getLoginPageUrl() !!}">
                    Sign in here
                </a>
            </h4>
        </div>
        <br>
        {{--<h4 class="text-center">Instant Social Sign In</h4>--}}
        <div class="panel-body social">
            @include('components.buttons.facebook-login-button')
            @include('components.buttons.google-login-button')
        </div>
        <div class="col-xs-12 col-sm-4 center-block">
            <hr class="omb_hrOr">
            <span class="omb_spanOr" style="color: black !important;" >or</span>
        </div>
        <div class="panel-body col-sm-offset-3">
            <div class="clearfix"></div>
            <div class="col-xs-12 col-sm-8 ">
                @include('errors.errors')
                {!! Form::open(['url' => \App\Buttons\Auth\RegistrationButton::PATH, 'method' => 'POST', 'class' => 'form-horizontal']) !!}
                <div class="form-group">
                    <label class="control-label col-xs-3">Username</label>
                    <div class="col-xs-9">
                        <div class="input-group" id="username-group">
                            <span class="input-group-addon"> <i class="fa fa-fw fa-user-md text-primary"></i></span>
                            {!! Form::text('user_login', Request::old('user_login', getUrlParam('user_login')), 
['class' => 'form-control', 'placeholder' => 'Username', 'id' => 'username-input']) !!}
                        </div>
                    </div>
                </div>
{{--
                <div class="form-group">
                    <label class="control-label col-xs-3">Display Name:</label>
                    <div class="col-xs-9">
                        <div class="input-group">
                            <span class="input-group-addon"> <i class="fa fa-fw fa-user-md text-primary"></i>
                            </span>
                            {!! Form::text('display_name', Request::old('display_name'), ['class' => 'form-control', 'placeholder' => 'Full Name']) !!}
                        </div>
                    </div>
                </div>
--}}
                <div class="form-group">
                    <label class="control-label col-xs-3">Email</label>

                    <div class="col-xs-9">
                        <div class="input-group" id="email-group">
                            <span class="input-group-addon"> <i class="fa fa-envelope text-primary"></i></span>
                            {!! Form::email('email', Request::old('email', getUserEmailFromUrlOrRedirectParameter()), 
['class' => 'form-control', 'placeholder' => 'Email', 'id' => 'email-input']) !!}
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-xs-3">Password</label>
                    <div class="col-xs-9">
                        <div class="input-group" id="password-group">
                            <span class="input-group-addon"><i class="fa fa-fw fa-key text-primary"></i></span>
                            {!! Form::password('user_pass', ['class' => 'form-control', 'placeholder' => 'Password', 'id' => 'password-input']) !!}
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-xs-3">Confirm Password</label>
                    <div class="col-xs-9">
                        <div class="input-group" 
                             id="password-confirm-group">
                            <span class="input-group-addon"><i class="fa fa-fw fa-key text-primary"></i></span>
                            {!! Form::password('user_pass_confirmation', ['class' => 'form-control', 'placeholder' =>
                             'Password', 'id' => 'password-confirm-input']) !!}
                        </div>
                    </div>
                </div>
                {{--<div class="alert alert-success">--}}
                    {{--<p><strong>Try {{app_display_name()}} 14 days for free!</strong></p>--}}
                    {{--<p>If you continue using {{app_display_name()}} after your trial, we'll charge US $9 to your credit card monthly.--}}
                        {{--Don't worry — we'll remind you a few days before your first payment.</p>--}}
                    {{--<p><small>Payments handled by Stripe. We don't store your card details.</small></p>--}}
                {{--</div>--}}
                {{--<div class="form-group">--}}
                    {{--<label class="control-label col-xs-3">Card Number</label>--}}

                    {{--<div class="col-xs-9">--}}
                        {{--<div class="input-group">--}}
                            {{--<span class="input-group-addon"> <i class="fa fa-fw fa-credit-card text-primary"></i>--}}
                            {{--</span>--}}
                            {{--{!! Form::text('card_number', null, [--}}
                                {{--'class' => 'form-control',--}}
                                {{--'placeholder' => 'Card Number',--}}
                                {{--'maxlength' => '16'--}}
                            {{--]) !!}--}}
                        {{--</div>--}}
                    {{--</div>--}}
                {{--</div>--}}
                {{--<div class="form-group">--}}
                    {{--<label class="control-label col-xs-3">CVC</label>--}}

                    {{--<div class="col-xs-9">--}}
                        {{--<div class="input-group">--}}
                            {{--<span class="input-group-addon"> <i class="fa fa-fw fa-credit-card text-primary"></i>--}}
                            {{--</span>--}}
                            {{--{!! Form::text('card_cvc', null, [--}}
                                {{--'class' => 'form-control',--}}
                                {{--'placeholder' => 'CVC',--}}
                                {{--'maxlength' => '4'--}}
                            {{--]) !!}--}}
                        {{--</div>--}}
                    {{--</div>--}}
                {{--</div>--}}
                {{--<div class="form-group">--}}
                    {{--<label class="control-label col-xs-3">Exp Month</label>--}}

                    {{--<div class="col-xs-9">--}}
                        {{--<div class="input-group">--}}
                            {{--<span class="input-group-addon"> <i class="fa fa-fw fa-credit-card text-primary"></i>--}}
                            {{--</span>--}}
                            {{--{!! Form::selectMonth('card_month', null, ['class' => 'form-control']) !!}--}}
                        {{--</div>--}}
                    {{--</div>--}}
                {{--</div>--}}

                {{--<div class="form-group">--}}
                    {{--<label class="control-label col-xs-3">Exp Year</label>--}}

                    {{--<div class="col-xs-9">--}}
                        {{--<div class="input-group">--}}
                            {{--<span class="input-group-addon"> <i class="fa fa-fw fa-credit-card text-primary"></i></span>--}}
                            {{--{!! Form::selectRange('card_year', date("Y"), date("Y") + 10, null, ['class' => 'form-control']); !!}--}}
                        {{--</div>--}}
                    {{--</div>--}}
                {{--</div>--}}
                {{--<div class="alert alert-info">--}}
                    {{--<p>Use your coupon here, instead of credit card or with credit card.</p>--}}
                {{--</div>--}}
                {{--<div class="form-group">--}}
                    {{--<label class="control-label col-xs-3">Coupon Code:</label>--}}

                    {{--<div class="col-xs-9">--}}
                        {{--<div class="input-group">--}}
                            {{--<span class="input-group-addon"> <i class="fa fa-fw fa-gift text-primary"></i>--}}
                            {{--</span>--}}
                            {{--{!! Form::text('coupon', null, [--}}
                                {{--'class' => 'form-control',--}}
                                {{--'placeholder' => 'Coupon Code'--}}
                            {{--]) !!}--}}
                        {{--</div>--}}
                    {{--</div>--}}
                {{--</div>--}}
                <div class="form-group">
                    <div class="col-xs-offset-3 col-xs-9">
                        <label class="checkbox-inline mar-left">
                            <input type="checkbox" name="terms" value="1" class="minimal-red" checked/>
                            &nbsp; I agree to the
                            <a target="_blank" class="forgot" href="{{home_page()}}/terms-of-service/">Terms of Service</a>.
                        </label>
                    </div>
                </div>
                <div class="form-group" id="submit-button-group">
                    <div class="col-xs-offset-3 col-xs-9">
                        {{ qm_csrf_field() }}
                        <input type="hidden" name="tz" id="tz">
                        {!! Form::submit('Register', ['class' => 'btn btn-primary', 'id' => 'submit-button']) !!}
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
            <div class="row omb_row-sm-offset-3">
                <div class="col-xs-12 col-sm-3"></div>
            </div>
        </div>
    </div>
@endsection
