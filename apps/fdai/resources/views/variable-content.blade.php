<!-- Start resources/views/variable-content.blade.php  -->
<?php /** @var \App\Models\Variable $model */ ?>
{{-- TODO: {{ $model->getWikipediaExtract() }}--}}
{{--{!! $model->getOutcomesImageBarChart($limit ?? null, $category ?? null) !!}--}}
{{--{!! $model->getPredictorsImageBarChart($limit ?? null, $category ?? null) !!}--}}
{!! \App\Charts\CorrelationCharts\CorrelationsNetworkGraphQMChart::generateInline($model, $modelariableCategoryName ?? null) !!}
{!! \App\Charts\CorrelationCharts\CorrelationsSankeyQMChart::generateInline($model, $modelariableCategoryName ?? null) !!}
{{--{!! \App\Tables\PredictorsTable::generateHtml($model, $modelariableCategoryName ?? null) !!}--}}
{{--{!! \App\Tables\OutcomesTable::generateHtml($model, $modelariableCategoryName ?? null) !!}--}}
@include('variable-ct-tables')
@if($model->isPredictor() && !$model->isOutcome())
    <h2 id="outcomes-heading" style="visibility: hidden;">Outcomes</h2>
    @component('section-header') Outcomes of {{ $model->getTitleAttribute() }} @endcomponent
    <p>Below is the degree of change seen after {{ $model->getTitleAttribute() }} is higher than average.</p>
    {!! $model->getOutcomeSearchHtml() !!}
@endif
@if($model->isOutcome())
    <h2 id="predictors-heading" style="visibility: hidden;">Predictors</h2>
    @component('section-header')  Predictors of {{ $model->getTitleAttribute() }} @endcomponent
    <p>Below is the change in {{ $model->getTitleAttribute() }} seen after the predictor is higher than average.</p>
    {!! $model->getPredictorSearchHtml() !!}
@endif

@include('variable-descriptive-charts')
@if($model->isPredictor() && !$model->isOutcome())
    @include('nutrition-label-outcomes')
@endif
@if($model->isOutcome())
    @include('nutrition-label-predictors')
@endif
{{--
@foreach($model->getCorrelations($limit ?? null, $category ?? null) as $c)
    @include('study-card')
@endforeach
--}}
{!! $model->getStatisticsTableHtml() !!}
@if(\App\Utils\AppMode::isApiRequest())
    <script>
        {!! \App\Files\JavaScript\VariableShowJavaScriptFile::generate($model) !!}
    </script>
@else
	@if( isset($js) )
		{!! $js !!}
	@else
		<script defer src="data.js"></script>
	@endif
@endif
<!-- Start resources/views/variable-content.blade.php  -->
