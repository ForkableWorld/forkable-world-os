@extends('layouts.default')

{{-- Page title --}}
@section('title')
    All Measurements History
    @parent
@stop

    {{-- Page content --}}
    @section('content')
            <!-- Main content -->
    <section class="content">
        <div class="row">
            <iframe id="iframe" src="https://web.quantimo.do/#/app/history-all?hideMenu=true"
                    frameborder="0" width="100%" height="5000">
            </iframe>
        </div>
        <!--row end-->
    </section>
@stop

{{-- page level scripts --}}
@section('footer_scripts')
    <script src="{{ qm_asset('js/quantimodo/ionic.js') }}" type="text/javascript"></script>
@stop
