@extends('layouts.default')

{{-- Page title --}}
@section('title')
    Track Anything
    @parent
@stop

    {{-- Page content --}}
    @section('content')
            <!-- Main content -->
    <section class="content">
        <div class="row">
            @if(!isset($category))
                <iframe id="iframe" src="https://web.quantimo.do/#/app/measurement-add-search?hideMenu=true"
                        frameborder="0" width="100%" height=3000>
                </iframe>
            @else
                <iframe id="iframe" src="https://web.quantimo.do/#/app/measurement-add-search?variableCategoryName={{ $category }}?hideMenu=true"
                        frameborder="0" width="100%" height=3000>
                </iframe>
            @endif
        </div>
        <!--row end-->
    </section>
@stop

{{-- page level scripts --}}
@section('footer_scripts')
    <script src="{{ qm_asset('js/quantimodo/ionic.js') }}" type="text/javascript"></script>
@stop
