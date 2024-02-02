<!-- Start resources/views/chip-search.blade.php -->
<?php /** @var \App\Buttons\QMButton[] $buttons */ ?>
@include('search-filter-input')
@isset($heading)
    <h2 style="text-align: center;" class="text-3xl mb-2 font-semibold leading-normal">
        {{ $heading }}
    </h2>
@endisset
@include('chips')
@include('not-found-box')
