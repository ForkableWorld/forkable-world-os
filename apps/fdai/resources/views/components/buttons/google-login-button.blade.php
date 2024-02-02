@if(!app('request')->input('excludeGoogle'))
    <div class="col-xs-12 col-sm-4 center-block">
        <a href="{!! googleLoginUrl() !!}"
           id="google-login-button"
           target="_top"
           class="btn btn-lg btn-block btn-google">
            <i class="fa fa-google-plus-square fa-lg"></i>
            <span>Google</span>
        </a>
    </div>
@endif
