@extends('layouts.default')

{{-- Page title --}}
@section('title')
    Manage Reminders
    @parent
@stop

    {{-- Page content --}}
    @section('content')
            <!-- Main content -->
    <section class="content">
        <div class="row" style="margin:0px;padding:0px;overflow:hidden">
            <iframe id="iframe" src="https://web.quantimo.do/#/app/reminders-manage?hideMenu=true"
                    frameborder="0" width="100%" height="600">
            </iframe>
        </div>
        <!--row end-->
    </section>
@stop

{{-- page level scripts --}}
@section('footer_scripts')
    <script src="{{ qm_asset('js/quantimodo/ionic.js') }}" type="text/javascript"></script>
@stop
