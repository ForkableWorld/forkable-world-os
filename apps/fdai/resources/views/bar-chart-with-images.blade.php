<!-- Start resources/views/bar-chart.blade.php -->
<?php /** @var \App\Charts\BarChartButton[] $buttons */ ?>
@if(count($buttons) > 10)
    @include('search-filter-script')
@endif
<div id="{{$searchId ?? $table ?? "no-search-id-provided"}}-list" class="bar-chart-with-images">
    @isset($title)<h2>{{ $title }}</h2>@endisset
    @isset($subTitle)<p>{{ $subTitle }}</p>@endisset
    @foreach( $buttons as $button )
        {!! $button->getBarWithImage() !!}
    @endforeach
</div>
<!-- End resources/views/bar-chart.blade.php -->
