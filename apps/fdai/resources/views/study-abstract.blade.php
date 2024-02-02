<?php /** @var \App\Traits\HasCorrelationCoefficient $c */ ?>
<p>
	{{ $c->getOptimalValueSentenceWithPercentChange() }}
</p>
<p>
	{{($c->typeIsIndividual()) ? "This data" : "Aggregated data from ".$c->getNumberOfUsers()." study participants" }}
	suggests with a {{ $c->getConfidenceLevel() }} degree of confidence
	{{$c->getPValueDataPointsOrNumberOfParticipantsFragment()}} that {{ $c->getCauseVariableDisplayName()}} has a
	{{ $c->getEffectSize()}} predictive relationship
	(R={{ $c->getCorrelationCoefficient(\App\VariableRelationships\QMVariableRelationship::SIG_FIGS)}})
	with {{ $c->effectNameWithSuffix()}}.
</p>
@if( $c->getDailyValuePredictingHighOutcome() !== null)
	<p>
		The highest quartile of {{$c->getEffectVariableDisplayName()}} measurements were observed following an average
		{{ $c->getDailyValuePredictingHighOutcomeString()}} {{ $c->getCauseVariableDisplayName().$c->getPerDaySentenceFragment() }}
		.
	</p>
@endif
@if( $c->getDailyValuePredictingHighOutcome() !== null)
	<p>
		The lowest quartile of {{ $c->getEffectVariableDisplayName() }} measurements were observed following
		an
		average {{ $c->causeValueUnitVariableName($c->getDailyValuePredictingLowOutcome()).$c->getPerDaySentenceFragment() }}
		.
	</p>
@endif
@if($c->getDailyValuePredictingLowOutcome() !== null)
	<p>
		After an onset delay of {{ $c->getOnsetDelayHumanString() }}, {{ $c->getEffectVariableDisplayName() }} is
		typically
		{{ ($c->getPredictsLowEffectChange() > 0) ? $c->getPredictsLowEffectChange()."% higher" :
		abs($c->getPredictsLowEffectChange())."% lower" }} than average over the
		{{ $c->getDurationOfActionHumanString() }} following around {{ $c->getDailyValuePredictingLowOutcomeString()}}
		{{ $c->getCauseVariableDisplayName() }}.
	</p>
@endif
