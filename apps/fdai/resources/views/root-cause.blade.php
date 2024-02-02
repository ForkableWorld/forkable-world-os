<?php /** @var \App\Variables\QMUserVariable $uv */ ?>
@extends('layouts.material-app', [
    'title' => $uv->getTitleAttribute(),
    'activePage' => 'root-cause',
])
@section('content')
    <div class="content">
        <div class="clearfix"></div>
        @include('flash::message')
        @include('root-cause-content')
    </div>
@endsection
