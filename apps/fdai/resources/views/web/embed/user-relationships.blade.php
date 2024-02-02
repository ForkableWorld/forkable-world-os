@extends('layouts.default')

{{-- Page title --}}
@section('title')
    Your Relationships
    @parent
@stop

{{-- Page content --}}
@section('content')
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <iframe src="/embeddable/?plugin=search-relationships&outcome=Overall%20Mood&commonOrUser=user"
                    frameborder="0" width="100%" height="3000">
            </iframe>
        </div>
        <!--row end-->
    </section>
@stop