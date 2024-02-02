<?php /** @var \App\Pages\StockPage $page */ ?>
<h2>{{ $page->getDailyReturnUserVariable()->mean }}% Average Daily Return</h2>


{!! $page->getDailyReturnUserVariable()->getPredictorsTableHtml() !!}

{!! $page->getHourlyDistributionCharts() !!}

{!! $page->getDailyReturnUserVariable()->getChartGroup()->getHtmlWithDynamicCharts(false) !!}