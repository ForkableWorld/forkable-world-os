<?php /** @var \App\Reports\RootCauseAnalysisSection $obj */ ?>
{!! $obj->getIntroductorySentenceHTML() !!}
{!! $obj->getPositiveUpVotedSectionHtml() !!}
{!! $obj->getNegativeUpVotedSectionHtml()  !!}
{!! $obj->getFlaggedHtml() !!}
{!! $obj->getNeedReviewHtml() !!}
