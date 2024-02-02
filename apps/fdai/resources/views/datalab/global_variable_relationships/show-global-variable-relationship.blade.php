<?php /** @var App\Models\GlobalVariableRelationship $aggregateCorrelation */ ?>
@extends('layouts.admin-lte-app', ['title' => $aggregateCorrelation->getTitleAttribute() ])

@section('content')
    @include('model-header')
   <div class="content">
        <div class="box box-primary">
            <div class="box-body">
                <div class="row" style="padding-left: 20px">
                    {!! $aggregateCorrelation->getShowContent() !!}
                </div>
            </div>
        </div>
        <div class="box box-primary">
            <div class="box-body">
                <div class="row" style="padding-left: 20px">
                    @include('datalab.global_variable_relationships.show_fields')
                    <a href="{{ route('datalab.aggregateCorrelations.index') }}" class="btn btn-default">Back</a>
                </div>
            </div>
        </div>
    </div>
@endsection
