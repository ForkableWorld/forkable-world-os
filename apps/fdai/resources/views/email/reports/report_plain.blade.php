<?php /** @var App\Reports\AnalyticalReport $report */ ?>
<div>
    {{ $report->getOrGenerateEmailHtml() }}
</div>
