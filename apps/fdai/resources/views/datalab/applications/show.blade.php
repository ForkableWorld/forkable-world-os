<?php /** @var App\Models\Application $application */ ?>
@extends('layouts.admin-lte-app', ['title' => $application->app_display_name ])

@section('content')
    @include('model-header')
   <div class="content">
        <div class="box box-primary">
            <div class="box-body">
                <div class="row" style="padding-left: 20px">
                    @include('datalab.applications.show_fields')
                    <a href="{{ route('datalab.applications.index') }}" class="btn btn-default">Back</a>
                </div>
            </div>
        </div>
    </div>
@endsection
