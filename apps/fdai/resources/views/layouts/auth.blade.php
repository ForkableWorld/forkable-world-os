<html>
<head>
    @include('meta')
    <!-- global level css -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    @include('fontawesome')
    <!-- end of global css-->
    <!-- page level styles-->
    <link href="{!! qm_asset('css/custom_css/login.css') !!}" rel="stylesheet" type="text/css">
    <!-- end of page level styles-->
    <!--page level css-->
    @yield('header_styles')
    <!--end of page level css-->
    @include('loggers-js')
</head>
<body>{!! Analytics::render() !!}
<div class="container">
    @yield('content')
</div>
<!-- global js -->
<script src="https://code.jquery.com/jquery-1.11.1.min.js" type="text/javascript"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js" type="text/javascript"></script>
@include('psychedelic-loader')
@include('moment-js')
@include('pace')
@include('sweetalert::alert')
@include('timezone')
@include('components.buttons.chat-button')
<!-- begin page level js -->
@yield('footer_scripts')
<!-- end page level js -->
</body>
</html>
