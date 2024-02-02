<?php /** @var App\Models\CommonTag $commonTag */ ?>
@extends('layouts.admin-lte-app', ['title' => null ])

@section('content')
    @include('model-header')
   <div class="content">
        <div class="box box-primary">
            <div class="box-body">
                <div class="row" style="padding-left: 20px">
                    @include('datalab.common_tags.show_fields')
                    <a href="{{ route('datalab.commonTags.index') }}" class="btn btn-default">Back</a>
                </div>
            </div>
        </div>
    </div>
@endsection
