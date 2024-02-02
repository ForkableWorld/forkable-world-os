<?php
/** @var App\Reports\RootCauseAnalysis $report */
$v = $report->getOutcomeQMUserVariable();
$u = $report->getQMUser();
?>
<div style="max-width: 980px; font-family: 'Source Sans Pro', sans-serif; margin: auto;">
   @if ( $v->isRating() )
        @component('components.cards.rating-faces-card')
            @slot('title')
                {{ $v->getQuestion() }}
            @endslot
            @slot('footer')
                If you'd prefer to optimize sometime besides {{ $v->getOrSetVariableDisplayName() }}
                 you can <a href="{{ \App\Utils\IonicHelper::getSettingsUrl() }}">update your primary outcome variable here</a>.
            @endslot
        @endcomponent
   @endif
    {!! $u->getDataQuantityListRoundedButtonsHTML() !!}
   {!! $report->getDemoOrUserFactorListForEmailHtml() !!}
   {!!  \App\Cards\HelpQMCard::instance()->getHtml(\App\UI\QMColor::HEX_DARK_GRAY) !!}
</div>
