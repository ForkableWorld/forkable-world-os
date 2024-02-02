<?php /** @var App\Models\User $user */ ?>
@extends('layouts.admin-lte-app', ['title' => $user->display_name ])

@section('content')
    @include('flash::message')
    <div class="row">
        <section class="content-header" style="min-height: 30px;">
            <h1 class="pull-left" style="padding: 10px;">
                @include('model-index-button')
            </h1>
            <h1 class="pull-right"  style="padding: 10px;">
                @include('model-edit-button')
                @include('model-show-button')
                @include('single-model-menu-button')
            </h1>
        </section>
    </div>
    <section class="content">
        @include('widget-and-tabs')
    </section>
    <div id='app'>
        @include('oauth-clients-for-user')
    </div>
@endsection
