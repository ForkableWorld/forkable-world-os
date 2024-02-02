<?php /** @var App\Reports\AnalyticalReport $report */ ?>

{{--

@component('wp.blocks.wp-cover-block')
    @slot('coverImageUrl')
        {{ $report->getCoverImage() }}
    @endslot
@endcomponent

--}}

@component('wp.blocks.wp-paragraph-block')
    @slot('paragraph')
        {{ $report->getSubtitleAttribute() }}
    @endslot
@endcomponent

@component('wp.blocks.wp-button-block')
    @slot('buttonTitle')
        Email Summary
    @endslot
    @slot('buttonLink')
        {{ $report->getEmailHtmlUrlLink() }}
    @endslot
@endcomponent

@component('wp.blocks.wp-button-block')
    @slot('buttonTitle')
        Full Report
    @endslot
    @slot('buttonLink')
        {{ $report->getFullHtmlUrlLink() }}
    @endslot
@endcomponent

@component('wp.blocks.wp-button-block')
    @slot('buttonTitle')
        PDF Report
    @endslot
    @slot('buttonLink')
        {{ $report->getPdfUrl() }}
    @endslot
@endcomponent


{{--

<!-- wp:html -->
{!! $report->getOrGenerateEmailHtml() !!}
<!-- /wp:html -->

--}}
