<!-- start resources/views/variable-correlation-tables-charts.blade.php -->
<?php /** @var \App\Models\Variable $v */ ?>
@php($v = $v ?? $variable ?? $model ?? null)
{!! \App\Charts\CorrelationCharts\CorrelationsNetworkGraphQMChart::generateInline($v, $variableCategoryName ?? null) !!}
{!! \App\Charts\CorrelationCharts\CorrelationsSankeyQMChart::generateInline($v, $variableCategoryName ?? null) !!}
{!! \App\Tables\PredictorsTable::generateHtml($v, $variableCategoryName ?? null) !!}
{!! \App\Tables\OutcomesTable::generateHtml($v, $variableCategoryName ?? null) !!}
<!-- end resources/views/variable-correlation-tables-charts.blade.php -->
