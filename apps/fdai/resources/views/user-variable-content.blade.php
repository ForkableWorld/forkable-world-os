<!-- Start resources/views/user-variable-content.blade.php  -->
<?php /** @var \App\Variables\QMUserVariable $uv */ ?>
{{--{!! \App\Charts\CorrelationCharts\CorrelationsNetworkGraphQMChart::generateInline($uv) !!}--}}
{{--{!! \App\Charts\CorrelationCharts\CorrelationsSankeyQMChart::generateInline($uv) !!}--}}
{!! $uv->getChartGroup()->getHtmlWithDynamicCharts($includeJS ?? false) !!}
{!! \App\Tables\PredictorsTable::generateHtml($uv) !!}
{!! \App\Tables\OutcomesTable::generateHtml($uv) !!}
{!! \App\Tables\CausesOfConditionTable::generateHtml($uv) !!}
{!! \App\Tables\ConditionsResultingFromCauseTable::generateHtml($uv) !!}
{!! \App\Tables\ConditionsTreatedTable::generateHtml($uv) !!}
{!! \App\Tables\SideEffectsFromTreatmentTable::generateHtml($uv) !!}
{!! \App\Tables\TreatmentsForConditionTable::generateHtml($uv) !!}
{!! \App\Tables\TreatmentsWithSideEffectTable::generateHtml($uv) !!}
{{--{!! \App\Charts\DistributionColumnChart::generateInline($uv) !!}--}}
{{--{!! \App\Charts\YearlyColumnChart::generateInline($uv) !!}--}}
{{--{!! \App\Charts\MonthlyColumnChart::generateInline($uv) !!}--}}
{{--{!! \App\Charts\WeekdayColumnChart::generateInline($uv) !!}--}}
@include('invalid-source-data', ['a' => $uv])
<!-- End resources/views/user-variable-content.blade.php  -->
