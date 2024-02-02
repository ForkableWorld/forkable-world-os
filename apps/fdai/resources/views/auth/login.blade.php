@extends('layouts.auth')
@section('title') <title>Login</title> @endsection
@section('content')
    <div class="row">
        <div class="panel-header">
            @if ( showLogo() )
                <img style="display: block; margin: 0 auto; max-width: 25%;" src="{{ text_logo() }}" alt="logo image">
            @endif
            <h1 class="text-center">Sign In</h1>
            <h4 class="text-center">Don't have an account?  </h4>
            <h4 class="text-center">
                <a id="register-page-link"
                    style="color: white !important; text-decoration: underline;"
                    href="{!! getRegisterUrl() !!}">
                    Sign up here
                </a>
            </h4>
        </div>
        <div class="panel-body social">
            @include('components.buttons.facebook-login-button')
            @include('components.buttons.google-login-button')
            <div class="clearfix">
                <div class="col-xs-12 col-sm-4 center-block"> <hr class="omb_hrOr">
                    <span style="color: black !important;" class="omb_spanOr">or</span>
                </div>
                <div class="clearfix"></div>
                <div class="col-xs-12 col-sm-4 center-block">
                    @include('errors.errors')
                    {!! Form::open(['url' => \App\Buttons\Auth\LoginButton::PATH, 'method' => 'POST']) !!}
                        <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-user"></i>
                                </span>
                            {!! Form::text('user_login', getUserEmailFromUrlOrRedirectParameter(), 
['class' => 'form-control', 'placeholder' => 'Username or Email', 'id' => 'username-input']) !!}
                        </div>
                        <span class="help-block"></span>
                        <div class="input-group">
                                <span class="input-group-addon"> <i class="fa fa-lock"></i> </span>
                            {!! Form::password('user_pass', ['class' => 'form-control', 'placeholder' => 'Password', 
'id' => 'password-input']) !!}
                        </div>
                        <span class="help-block"></span>
                        <input type="hidden" name="tz" id="tz">
                        <div class="checkbox"> <label> <input type="checkbox"> Remember Me </label> </div>
                        {{ qm_csrf_field() }}
                        {!! Form::submit('Login', ['class' => 'btn btn-md btn-primary btn-block', 'id' => 
                        "submit-button-group", 'id' => 'login-button']) !!}
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
        <div  class="forgot">
            <a id="forgot-password-link"
                style="color: white !important;"
                href="{!! url('auth/password/reset') !!}">
                Forgot your password?
            </a>
        </div>
        <br>
        <div style="text-align: center">
            <label class="checkbox-inline mar-left">
                <input type="checkbox" name="terms" value="1" class="minimal-red" checked/>
                &nbsp; I agree to the
                <a target="_blank" class="forgot" href="{{ home_page() }}/terms-of-service/">Terms of Service</a>.
            </label>
        </div>
    </div>
@endsection
