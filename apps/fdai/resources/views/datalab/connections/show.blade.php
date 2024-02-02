<?php /** @var App\Models\Connection $connection */ ?>
@extends('layouts.admin-lte-app', ['title' => $connection->getTitleAttribute() ])

@section('content')
    @include('model-header')
   <div class="content">
        <div class="box box-primary">
            <div class="box-body">
                <div class="row" style="padding-left: 20px">
                    <h1> {{ $model->getTitleAttribute() }}</h1>
                    @include('datalab.connections.show_fields')
                    <a href="{{ route('datalab.connections.index') }}" class="btn btn-default">Back</a>
                </div>
            </div>
        </div>
    </div>
@endsection
