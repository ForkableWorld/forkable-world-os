<!-- start resources/views/variable-ct-tables.blade.php -->
<?php /** @var \App\Models\Variable $v */ ?>
@php($v = $v ?? $variable ?? $model ?? null)
{!! \App\Charts\DistributionColumnChart::generateInline($v) !!}
{!! \App\Charts\YearlyColumnChart::generateInline($v) !!}
{!! \App\Charts\MonthlyColumnChart::generateInline($v) !!}
{!! \App\Charts\WeekdayColumnChart::generateInline($v) !!}
<!-- end resources/views/variable-ct-tables.blade.php -->
