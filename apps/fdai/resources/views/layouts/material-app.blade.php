<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
@include('meta')
<meta name="csrf-token" content="{{ qm_csrf_token() }}">
<meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no' name='viewport' />
<!--     Fonts and icons     -->
<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css">
<!-- CSS Files -->
@include('fontawesome')
<link href="https://static.quantimo.do/material/css/material-dashboard.css?v=2.1.1" rel="stylesheet" />
<link href="https://static.quantimo.do/lib/sortable/css/sortable-theme-minimal.css" rel="stylesheet" />
<!-- CSS Just for demo purpose, don't include it in your project -->
<link href="{{ qm_asset('material') }}/demo/demo.css" rel="stylesheet" />
<!-- Sortable tables -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.20/css/jquery.dataTables.css">
<!-- HEAD JS -->
<script src="{{ qm_asset('material') }}/js/core/jquery.min.js"></script>
@include('psychedelic-loader')
</head>
<body class="{{ $class ?? '' }}">
@include('hidden-logout-form')
    <div class="wrapper">
        @include('material-sidebar')
        <div class="main-panel">
            @include('material-nav-bar')
            @yield('content')
            @include('footer')
        </div>
    </div>

    @if( $settingsImplemented = false )
        @include('material-theme-settings')
    @endif
	@include('javascript-in-body')     <!--   Bootstrap has to be loaded before the below libraries  -->
    <!--   Core JS Files   -->
    <script src="{{ qm_asset('material') }}/js/core/popper.min.js"></script>
    <script src="{{ qm_asset('material') }}/js/core/bootstrap-material-design.min.js"></script>
{{--  Not sure why this was necessary but messes up regular scrollbar      <script src="{{ qm_asset('material') }}/js/plugins/perfect-scrollbar.jquery.min.js"></script>--}}
    <!-- Plugin for the momentJs  -->
    <script src="{{ qm_asset('material') }}/js/plugins/moment.min.js"></script>
    <!-- Forms Validations Plugin -->
    <script src="{{ qm_asset('material') }}/js/plugins/jquery.validate.min.js"></script>
    <!-- Plugin for the Wizard, full documentation here: https://github.com/VinceG/twitter-bootstrap-wizard -->
    <script src="{{ qm_asset('material') }}/js/plugins/jquery.bootstrap-wizard.js"></script>
    <!--	Plugin for Select, full documentation here: http://silviomoreto.github.io/bootstrap-select -->
    <script src="{{ qm_asset('material') }}/js/plugins/bootstrap-selectpicker.js"></script>
    <!--  Plugin for the DateTimePicker, full documentation here: https://eonasdan.github.io/bootstrap-datetimepicker/ -->
    <script src="{{ qm_asset('material') }}/js/plugins/bootstrap-datetimepicker.min.js"></script>
    <!--	Plugin for Tags, full documentation here: https://github.com/bootstrap-tagsinput/bootstrap-tagsinputs  -->
    <script src="{{ qm_asset('material') }}/js/plugins/bootstrap-tagsinput.js"></script>
    <!-- Plugin for Fileupload, full documentation here: http://www.jasny.net/bootstrap/javascript/#fileinput -->
    <script src="{{ qm_asset('material') }}/js/plugins/jasny-bootstrap.min.js"></script>
    <!--  Full Calendar Plugin, full documentation here: https://github.com/fullcalendar/fullcalendar    -->
    <script src="{{ qm_asset('material') }}/js/plugins/fullcalendar.min.js"></script>
    <!-- Vector Map plugin, full documentation here: http://jvectormap.com/documentation/ -->
    <script src="{{ qm_asset('material') }}/js/plugins/jquery-jvectormap.js"></script>
    <!--  Plugin for the Sliders, full documentation here: http://refreshless.com/nouislider/ -->
    <script src="{{ qm_asset('material') }}/js/plugins/nouislider.min.js"></script>
    <!-- Include a polyfill for ES6 Promises (optional) for IE11, UC Browser and Android browser support SweetAlert -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/core-js/2.4.1/core.js"></script>
    <!-- Library for adding dinamically elements -->
    <script src="{{ qm_asset('material') }}/js/plugins/arrive.min.js"></script>
    <!-- Chartist JS -->
    <script src="{{ qm_asset('material') }}/js/plugins/chartist.min.js"></script>
    <!--  Notifications Plugin    -->
    <script src="{{ qm_asset('material') }}/js/plugins/bootstrap-notify.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sql-formatter@2.3.3/dist/sql-formatter.js" integrity="sha256-GW+DLjF7aHOKOlLZJ5iL2dM2O7I+w9RM1UgG/IXFkiA=" crossorigin="anonymous"></script>
    <!-- Control Center for Material Dashboard: parallax effects, scripts for the example pages etc -->
    <script src="https://static.quantimo.do/material/js/material-dashboard.js?v=2.1.1" type="text/javascript"></script>


    <!-- Material Dashboard DEMO methods, don't include it in your project! -->
    <script src="{{ qm_asset('material') }}/demo/demo.js"></script>
    <script src="{{ qm_asset('material') }}/js/settings.js"></script>
{{--        @include('ionic-ball')--}}
@stack('js')
</body>
</html>
