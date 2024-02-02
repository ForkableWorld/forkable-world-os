<?php /** @var \App\Studies\QMUserStudy $model */ ?>
@if($c = $model->getQMUserVariableRelationshipIfPossible())
    {!! $model->getGaugeAndImagesWithTagLine() !!}
    {!! $model->getJoinButtonHTML() !!}
<div id="charts-container">
    @include('h2-heading-bar', ['title' => "Charts"])
    {!! $model->getCauseUserVariable()->getChartsButtonHtml() !!}
    {!! $model->getEffectUserVariable()->getChartsButtonHtml() !!}
    {!! \App\Charts\CorrelationCharts\PredictorDistributionColumnChart::generateInline($c) !!}
    {!! \App\Charts\CorrelationCharts\OutcomeDistributionColumnChart::generateInline($c) !!}
    {!! \App\Charts\CorrelationCharts\UserVariableRelationshipScatterPlot::generateInline($c) !!}
    {!! \App\Charts\CorrelationCharts\CorrelationsOverDurationsOfActionChart::generateInline($c) !!}
    {!! \App\Charts\CorrelationCharts\CorrelationsOverOnsetDelaysChart::generateInline($c) !!}
    {!! \App\Charts\CorrelationCharts\PairsOverTimeLineChart::generateInline($c) !!}
    {!! \App\Charts\CorrelationCharts\UnpairedOverTimeLineChart::generateInline($c) !!}
</div>
@else
    {!! $model->getJoinButtonHTML() !!}
    @php($causeCharts = \App\Charts\UserVariableCharts\UserVariableChartGroup::generateInline($model->getCauseQMUserVariable()))
    @php($effectCharts = \App\Charts\UserVariableCharts\UserVariableChartGroup::generateInline($model->getEffectQMUserVariable()))
    <div id="charts-container">
	    @if($causeCharts)
	        @include('h2-heading-bar', ['title' => $model->getCauseVariableName(). " Charts"])
	        {!! $causeCharts !!}
	    @endif
	    @if($effectCharts)
	        @include('h2-heading-bar', ['title' => $model->getEffectVariableName(). " Charts"])
	        {!! $effectCharts !!}
		@endif
    </div>
@endif
{!! $model->getStudyHtml()->getStudyTextHtml() !!}
{!! $model->getStudyHtml()->getStatisticsTable() !!}
