<!-- Start resources/views/bar-chart.blade.php -->
<?php /** @var \App\Charts\BarChartButton[] $buttons */ ?>
@include('search-filter-input') {{-- Make sure to wrap items with an <a> tag so they're hidden --}}
<div id="{{$searchId ?? $table ?? "no-search-id-provided"}}-list" class="bar-chart-full-width">
    @isset($title)<h2>{{ $title }}</h2>@endisset
    @isset($subTitle)<p>{{ $subTitle }}</p>@endisset
    @foreach( $buttons as $button )
        @include('bar-chart-bar-full-width')
    @endforeach
</div>
<!-- End resources/views/bar-chart.blade.php -->
