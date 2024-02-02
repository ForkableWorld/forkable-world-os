<?php /** @var App\Models\VariableCategory $variableCategory */ ?>
@extends('layouts.admin-lte-app', ['title' => $variableCategory->name ])

@section('content')
    @include('model-header')
   <div class="content">
        <div class="box box-primary">
            <div class="box-body">
                <div class="row" style="padding-left: 20px">
                    @include('datalab.variable_categories.show_fields')
                    <a href="{{ route('datalab.variableCategories.index') }}" class="btn btn-default">Back</a>
                </div>
            </div>
        </div>
    </div>
@endsection
