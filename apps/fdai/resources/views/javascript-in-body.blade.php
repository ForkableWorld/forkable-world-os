<!-- Start resources/views/javascript-in-body.blade.php -->
<script src="https://code.jquery.com/jquery-3.1.1.min.js" type="text/javascript"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js" type="text/javascript"></script>
<script src="https://adminlte.io/themes/AdminLTE/bower_components/PACE/pace.min.js"></script>
<script defer src="https://static.quantimo.do/data/commonVariables.js"></script>
<script src="https://static.quantimo.do/lib/q/q.js"></script>
<script src="{{ public_app_public_url('js/qmHelpers.js') }}"></script>
@include('loggers-js')
@include('highcharts-js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
<script>
    $('#flash-overlay-modal').modal();
</script>
<!-- at the bottom of the page -->
<script type="text/javascript">
    window.mdc && window.mdc.autoInit();
</script>
@include('datatables_js')
<script src="https://static.quantimo.do/lib/sortable/js/sortable.js" defer></script>
<script src="{{ public_app_public_url('sidebar-menu/menu-search-filter.js') }}" defer></script>

<script src="https://static.quantimo.do/js/share.js"></script> <!-- https://github.com/jorenvh/laravel-share -->
{{--@livewireScripts what do we use livewireScripts for? --}}
{{--
    @include('components.buttons.chat-button')
    @include('ionic-ball')
--}}
{{--    @include('ionic-ball')--}}
<script type="text/javascript">
    // To make Pace works on Ajax calls
    $(document).ajaxStart(function () {Pace.restart()})
</script>
@include('sweetalert::alert')
<script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>
@stack('scripts')
<!-- End resources/views/javascript-in-body.blade.php -->
