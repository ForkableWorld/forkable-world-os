<?php /** @var \App\Variables\QMUserVariable $uv */ ?>
@include('root-cause-intro')
{!! $uv->getUser()->getDataQuantityListRoundedButtonsHTML() !!}
{!! $uv->getChartGroup()->getHtmlWithDynamicCharts(false) !!}--}}
{!! $uv->getPredictorsTableHtml() !!}
