<?php /** @noinspection PhpFullyQualifiedNameUsageInspection */
/** @var \App\Buttons\QMButton[] $buttons */
?>
@extends('ifttt.ifttt-layout')
<div class="my_services_view__my-services-container__25rBm"><h1>Your world works better together</h1>
    <div class="ifttt-next-search">
        <input id="search" class="ifttt-next-search-input" type="text"
               placeholder="Filter services" maxlength="1024" autocomplete="off"
               value="">
    </div>
    <ul class="my_services_view__my-services-list__3gBNg">
        @foreach($buttons as $button)
            @include( 'ifttt.round-button' )
        @endforeach
    </ul>
    <button class="btn my_services_view__my-services-btn__nHz1F">View all</button>
    <div class="breath-space"></div>
</div>
