<?php /** @var \App\Reports\RootCauseAnalysisSection $section */ ?>

<p>You don't have any verified studies regarding
    {{ $section->getPredictorVariableCategoryName(true) }} that are {{ $relationshipFragment }}
    {{ $section->getOutcomeVariableName() }}
</p>
<p>
    This can happen for a few reasons:
</p>
<ul class="list-disc">
    <li>
        You don't have enough data.  If this is the case, please
        {!! Html::link(ionic_url(), "import your data and start tracking.") !!}
    </li>
    <li>
        You haven't reviewed and verified your studies yet.  Check the Un-Reviewed Studies section below.
    </li>
</ul>
<p>
    If you need any help, please {!! \App\UI\HtmlHelper::getHelpLinkAnchorHtml("contact us.") !!}
</p>
<p>Until you record or import enough data, you can check out this report containing anonymously donated data:
    <a href="{{\App\Reports\RootCauseAnalysis::EXAMPLE_PDF}}">
       Download Example Report
    </a>
</p>
