@extends('layouts.default')

{{-- Page title --}}
@section('title')
    API Explorer
    @parent
@stop
{{-- Page content --}}
@section('content')
    <section class="content"> <div class="row"> <iframe src="https://docs.quantimo.do/" frameborder="0" width="100%" height="5500"></iframe> </div> </section>
@stop
