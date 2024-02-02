<?php /** @var \App\Pages\StockPage $page */ ?>
@extends('layouts.material-app', ['title' => $page->getTitleAttribute(), 'activePage' => \App\Buttons\MoneyModo\StockButton::PATH, ])
@section('content')
    <div class="content">
        <div class="clearfix"></div>
        @include('flash::message')
        @include('stock-content')
    </div>
@endsection
