<!-- start resources/views/variable-descriptive-charts.blade.php -->
<?php /** @var \App\Models\Variable $v */ ?>
@php($v = $v ?? $variable ?? $model ?? null)
{!! \App\Tables\CausesOfConditionTable::generateHtml($v) !!}
{!! \App\Tables\ConditionsResultingFromCauseTable::generateHtml($v) !!}
{!! \App\Tables\ConditionsTreatedTable::generateHtml($v) !!}
{!! \App\Tables\SideEffectsFromTreatmentTable::generateHtml($v) !!}
{!! \App\Tables\TreatmentsForConditionTable::generateHtml($v) !!}
{!! \App\Tables\TreatmentsWithSideEffectTable::generateHtml($v) !!}
<!-- end resources/views/variable-descriptive-charts.blade.php -->
