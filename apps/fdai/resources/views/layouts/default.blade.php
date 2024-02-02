<?php $pluginUrl = '/'.\App\Repos\QMWPPluginRepo::URL_PATH; ?>
        <!DOCTYPE html>
<html>
<head>
    @include('meta')
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="http://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="http://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
    <!-- global css -->
    @include('fontawesome')
    <link href="{{ qm_asset('/css/bootstrap.min.css') }}"
          rel="stylesheet"
          type="text/css"/>
    <link href="{{ qm_asset('/css/custom_css/chandra.css') }}"
          rel="stylesheet"
          type="text/css"/>
    <link href="{{ qm_asset('/css/custom_css/metisMenu.css') }}"
          rel="stylesheet"
          type="text/css"/>
    <link href="{{ qm_asset('/css/custom_css/panel.css') }}"
          rel="stylesheet"
          type="text/css"/>
    <link href="{{ qm_asset('/css/custom_css/alertmessage.css') }}"
          rel="stylesheet"
          type="text/css"/>
    <link href="{{ qm_asset('/css/custom_css/skins/skin-quantimodo.css') }}"
          rel="stylesheet"
          type="text/css"/>
    <link href="{{ qm_asset('/css/jquery-ui-1.10.4.custom.min.css') }}"
          rel="stylesheet"
          type="text/css"/>
    <!-- For Ionic Ball -->
    <link href="https://static.quantimo.do/qm-connect/embed/qm-ionic-embed.css"
          rel="stylesheet"
          type="text/css"/>
    @include('loggers-js')
    <!-- start CSFR: Uses jQuery and meta csrf-token to setup CSRF protection https://laravel.com/docs/5.7/csrf  -->
    <script src="{{ qm_asset('/js/jquery-1.11.1.min.js') }}"
            type="text/javascript"></script>
    <meta name="csrf-token"
          content="{{ csrf_token() }}">
    <script>$.ajaxSetup({headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}});</script>
    <!-- end CSFR -->
    <!-- end of global css -->
    <!--page level css-->
@yield('header_styles')
<!--end of page level css-->
</head>
<body class="skin-chandra">{!! Analytics::render() !!}
<!-- header logo: style can be found in header-->
<header class="header">
    @if (!app('request')->input('hideMenu'))
        <nav class="navbar navbar-static-top"
             role="navigation">
            <div class="navbar-right">
                <ul class="nav navbar-nav">
                    <!-- User Account: style can be found in dropdown-->
                    <li class="dropdown user user-menu">
                        <a id="avatar-anchor-right"
                           href="#"
                           class="dropdown-toggle padding-user"
                           data-toggle="dropdown">
                            <img id="avatar-image-right"
                                 src="{!! Auth::user()->avatar_image !!}"
                                 alt="avatar image"
                                 class="img-circle img-responsive pull-left"
                                 style="
                                    display: block;
                                    max-height: 35px;
                                    max-width: 35px;
                                    width: auto;
                                    height: auto;
                                    border-radius: unset;
                                    -webkit-border-radius: unset;
                                "
                                 height="35"
                                 width="35"/>
                            <div class="riot">
                                <div> {{ Auth::user()->display_name }} <span><i class="caret"></i> </span></div>
                            </div>
                        </a>
                        <ul class="dropdown-menu">
                            <!-- User image -->
                            <li class="user-header">
                                <img src="{!! Auth::user()->avatar_image !!}"
                                     alt="avatar image"
                                     style="display: block; max-height: 35px; max-width: 35px; width: auto; height: auto;"
                                     class="img-circle img-bor"/>
                                <p style="width: 100%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ Auth::user()->display_name }}
                                </p>
                            </li>
                            <!-- Menu Body -->
                            <li role="presentation"></li>
                            @include('menus.account-menu-items')
                            @include('menus.items.help-menu-item')
                            <li role="presentation"
                                class="divider"></li>
                            <!-- Menu Footer-->
                            <li class="user-footer">
                                <div class="pull-right">
                                    <a id="dropdown-user-menu-logout-button"
	                                    href="{{ URL::to('auth/logout') }}">
	                                    <i class="fa fa-fw fa-sign-out"></i>
                                        Logout
                                    </a>
                                </div>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
            @if(text_logo() && strpos(text_logo(), 'placehold') === false)
                {
                <a href="{{ route('account') }}"
                   class="logo">
                    <!-- Add the class icon to your logo image or logo icon to add the margining -->
                    {{--<img src="{{text_logo()}}" alt="logo"/>--}}
                    <a href="#"
                       class="navbar-btn sidebar-toggle"
                       data-toggle="offcanvas"
                       role="button"><i class="fa fa-fw fa-hand-o-left"></i></a>
                </a>
            @else
                {{--<h3 style="padding-left: 20px; color: white;">{{app_display_name()}}</h3>--}}
            @endif

            {{-- <div id="qm-ionic-button-holder"><img id="qm-ionic-app-show-hide" src="{{  qm_asset($pluginUrl . 'images/quantimodo.png')}}"></img></div>--}}
            <div>
                {{-- <a href="{{ route('account') }}" class="get-started"> Account </a>--}}
                @if (!empty(Cookie::get('physician')))
                    <a href="{{ route('account.back') }}"
                       class="get-started">Acting as {{ Auth::user()->display_name }}. Click to switch to your account.
                    </a>
                @endif
            </div>
        </nav>
    @endif
</header>
<div class="wrapper row-offcanvas row-offcanvas-left">
    <!-- Left side column. contains the logo and sidebar -->
    <aside class="left-side sidebar-offcanvas">
        <!-- sidebar: style can be found in sidebar-->
        <section class="sidebar">
            <div id="menu"
                 role="navigation">
                <div class="nav_profile">
                    <div class="media profile-left">
                        <a href="{{ route('account') }}">
                            <img id="left-profile-avatar-thumb"
                                src="{!! Auth::user()->avatar_image !!}"
                                 alt="avatar image"
                                 class="pull-left profile-thumb"
                                 style="
                                    display: block;
                                    max-height: 70px;
                                    max-width: 70px;
                                    width: auto;
                                    height: auto;
                                    padding-top: 0;
                                    padding-right: 10px;
                                    border-radius: unset;
                                    -webkit-border-radius: unset;
                                "
                            />
                        </a>
                        <div class="content-profile">
                            <h4 class="media-heading"> {{ Auth::user()->display_name }} </h4>
                            <ul class="icon-list">
                                <li>
                                    <a href="{{ route('account.edit') }}"><i class="fa fa-fw fa-gear"></i></a>
                                </li>
                                <li>
                                    <a id="sidebar-nav-profile-logout-button"
	                                    href="{{ URL::to('auth/logout') }}">
	                                    <i class="fa fa-fw fa-sign-out"></i>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                @include('menus.chandra-menu')
            </div>
            <!-- menu -->
        </section>
        <!-- /.sidebar -->
    </aside>
    <aside class="right-side right-padding">
        <!-- Notifications -->
    @include('notifications')
    <!-- Content -->
    @yield('content')
    <!-- /.content -->
    </aside>
    <!-- /.right-side -->
</div>
<!-- /.right-side -->
<!-- ./wrapper -->
<div class="modal fade"
     id="delete_confirm"
     tabindex="-1"
     role="dialog"
     aria-labelledby="user_delete_confirm_title"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content"></div>
    </div>
</div>
<!-- global js -->
<script src="{{ qm_asset('/js/custom_js/jquery.ui.min.js') }}"
        type="text/javascript"></script>
<script src="{{ qm_asset('/js/jquery.form.min.js') }}"
        type="text/javascript"></script>
<script src="{{ qm_asset('/js/bootstrap.min.js') }}"
        type="text/javascript"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.20.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.14/moment-timezone-with-data-2012-2022.min.js"></script>
<script src="{{ qm_api_asset('/js/custom_js/app.js') }}"
        type="text/javascript"></script>
<script src="{{ qm_asset('/js/custom_js/metisMenu.js') }}"
        type="text/javascript"></script>
<script src="{{ qm_asset('/js/custom_js/rightside_bar.js') }}"
        type="text/javascript"></script>
<script src="https://unpkg.com/clipboard@2/dist/clipboard.min.js"></script>
{{--@include('components.ionic-ball')--}}
@include('components.buttons.chat-button')
@yield('footer_scripts')
<!-- end page level js -->
</body>
</html>
