<?php
use App\Utils\QMRoute;
$route = $route ?? qm_request()->getDataLabRouteName();
if(!isset($table)){
    $table = qm_request()->getTable();
    $viewPath = qm_request()->getViewPath();
    $pluralClassName = qm_request()->getPluralClassName();
    $title = qm_request()->getPluralTitleWithHumanizedQuery();
}
/** @var \App\Models\BaseModel $fullClassName */
$fullClassName = qm_request()->getFullClassFromRoute();
$analyzable = QMRoute::isAnalysisProgress();
$trash = QMRoute::isTrash();
?>
@extends('layouts.admin-lte-app', ['title' => $title ])
@section('content')
    @include('index-header')
   <div class="content">
        <div class="clearfix"></div>
        @include('flash::message')
       @if( $trash )
           @include('chart-deleted-at')
       @endif
        <div class="box box-primary">
            <div class="box-body">
                @include("table")
            </div>
        </div>
        <div class="text-center"></div>
    </div>
@endsection
