<?php /** @var \App\Studies\QMPopulationStudy $model */ ?>
{!! $model->getGaugeAndImagesWithTagLine() !!}
{!! $model->getJoinButtonHTML() !!}
@if( $ac = $model->findGlobalVariableRelationship() )
<div id="charts-container">
    @include('h2-heading-bar', ['title' => "Charts"])
    {!! \App\Charts\GlobalVariableRelationshipCharts\PopulationTraitCorrelationScatterPlot::generateInline($ac) !!}

    @include('h2-heading-bar', ['title' => $ac->getCauseVariableDisplayName()." Charts"])
    {!! \App\Charts\VariableCharts\VariableChartChartGroup::generateDescriptiveCharts($ac->getCauseVariable()) !!}

    @include('h2-heading-bar', ['title' => $ac->getEffectVariableDisplayName()." Charts"])
    {!! \App\Charts\VariableCharts\VariableChartChartGroup::generateDescriptiveCharts($ac->getEffectVariable()) !!}

    @if($mike = $ac->getMikesCorrelation())
        @include('h2-heading-bar', ['title' => "Relationship Charts"])
{{--        {!! \App\Charts\CorrelationCharts\CorrelationChartGroup::generateInline($mike) !!}--}}
        {!! \App\Charts\CorrelationCharts\CorrelationChartGroup::generateNonTemporalCharts($mike) !!}
    @endif
</div>
@endif
{!! $model->getStudyHtml()->getStudyTextHtml() !!}
{!! $model->getStudyHtml()->getStatisticsTable() !!}
