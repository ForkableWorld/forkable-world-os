<?php /** @var \App\Studies\QMStudy $study */ ?>

<div class="join-study-button wp-block-button"
     style="text-align: center; padding: 10px;">
    <a class="join-study-button-button-link wp-block-button__link"
       href="{{ $study->getStudyLinks()->getStudyJoinUrl() }}">
        Join This Study
    </a>
</div>

<div id="study-charts-section">
    {!! $study->getCauseVariableChartsButtonHtml() !!}
    {!! $study->getEffectVariableChartsButtonHtml() !!}
    {!! $study->getOrSetCharts()->getChartHtmlWithEmbeddedImageOrReasonForFailure() !!}
</div>

<div id="study-text-section">
    {!! $study->getStudyHtml()->getStudyText() !!}
</div>

<div id="statistics-table-section">
    {!! $study->getStudyHtml()->getStatisticsTable() !!}
</div>

<div id="participant-instructions-section">
    {!! $study->getStudyHtml()->getStatisticsTable() !!}
</div>

<div id="study-sharing-section">
    {!! $study->getOrAddSocialSharingButtons() !!}
</div>
