@extends('layouts.default')

{{-- Page title --}}
@section('title')
    Variables
    @parent
@stop

{{-- Page content --}}
@section('content')
            <!-- Main content -->
    <section class="content">
        <div class="row">
            <iframe src="/embeddable/?plugin=search-variables"
                    frameborder="0" width="100%" height="1500">
            </iframe>
        </div>
        <!--row end-->
    </section>
@stop