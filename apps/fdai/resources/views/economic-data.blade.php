<?php
?>
@extends('layouts.material-app', [
    'title' => (new \App\Buttons\MoneyModo\EconomicDataButton())->getTitleAttribute(),
    'activePage' => \App\Buttons\MoneyModo\EconomicDataButton::PATH,
])
@section('content')
    <div class="content">
        <div class="clearfix"></div>
        @include('flash::message')
        <div class="row">
            <div class="col-md-12">
                {!! \App\Models\User::econ()->getDataLabRelationshipCountCardsHtml() !!}
            </div>
        </div>
        <div class="text-center"></div>
    </div>
@endsection
