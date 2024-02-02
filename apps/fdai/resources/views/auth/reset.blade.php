@extends('layouts.auth')

@section('title')
    <title>Forgot Password</title>
@endsection

@section('content')
    <div class="row">
        <div class="panel-header">
            <div class="logo">
                <a id="login-page-link"
                    href="{!! getLoginPageUrl() !!}">
                    <img src="https://static.quantimo.do/img/QM-LOGO-black-300x85.png" alt="logo" height="59px" />
                </a>
            </div>
            <h2 class="text-center">
                Reset Password
            </h2>
        </div>
        <div class="panel-body social">
            <div class="clearfix">
                <div class="col-xs-12 col-sm-4 center-block">
                    <!-- Notifications -->
                    @include('notifications')

                    <form action="/auth/password/reset" class="form-horizontal"  autocomplete="off" method="POST">
                        {!! Form::token() !!}
                        <input type="hidden" name="token" value="{{ $token }}">
                        <div class="form-group {{ $errors->first('email', 'has-error') }}">
                            <label class="control-label col-md-5" for="email">Email</label>
                            <div class="col-md-7">
                                <input type="email" class="form-control" name="email">
                            </div>
                        </div>

                        <div class="form-group {{ $errors->first('password', 'has-error') }}">
                            <label class="control-label col-md-5" for="password">Password</label>
                            <div class="col-md-7">
                                <input type="password" class="form-control" name="password">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-5" for="password">Confirm Password</label>
                            <div class="col-md-7">
                                <input type="password" class="form-control" name="password_confirmation">
                            </div>
                        </div>
                        <span class="help-block">{{ $errors->first('password', ':message') }}</span>
                        <div class="form-group">
                            <div class="col-md-offset-5 col-md-7">
                                <input type="submit" class="btn btn-primary" value="Submit" />
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
