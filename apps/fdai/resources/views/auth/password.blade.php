@extends('layouts.auth')
@section('title')<title>Forgot Password</title>@endsection
@section('content')
    <div class="row">
        <div class="panel-header">
            <div class="logo">
                <a href="{!! getLoginPageUrl() !!}">
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
                    @if ( isset($errors) && $errors->any() )
                        <div class="alert alert-danger alert-dismissable">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            {{ $errors->first('email', ':message') }}  Please try again or contact help@curedao.org for help.
                        </div>
                    @endif
                    <form action="" class="omb_loginForm" autocomplete="off" method="POST">
                        {!! Form::token() !!}
                        <div class="input-group {{ $errors->first('email', 'has-error') }}">
                                    <span class="input-group-addon">
                                        <i class="fa fa-user"></i>
                                    </span>
                            <input type="email" class="form-control" name="email" placeholder="Enter email address" value="{!! Request::old('email') !!}"></div>
                        <br>
                        {{ qm_csrf_field() }}
                        <input type="submit" class="btn btn-md btn-primary btn-block" value="Send" />
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
