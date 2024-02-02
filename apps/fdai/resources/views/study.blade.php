<?php /** @var \App\Studies\QMStudy $s */ ?>
    <!DOCTYPE html>
<!--suppress UnterminatedStatementJS -->
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ MetaTag::get('title') }}</title>
{!! MetaTag::tag('description') !!}
{!! MetaTag::tag('image') !!}
{!! MetaTag::openGraph() !!}
{!! MetaTag::twitterCard() !!}
{{--Set default share picture after custom section pictures--}}
{!! MetaTag::tag('image', default_sharing_image()) !!}
<link rel="canonical" href="{{$s->getStudyLinkStatic()}}" />
<meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
<meta name="csrf-token" content="{{ qm_csrf_token() }}">
@if( env_is_testing() )
    <link rel="shortcut icon" href="{{ \App\UI\ImageUrls::PHPSTORM }}">
@else
    <link rel="shortcut icon" href="{{ qm_asset('img/admin-favicon.png') }}">
@endif
@include('css')
@include('javascript-in-head')
</head>
<div style="max-width: 600px;">
    <div class="join-study-button wp-block-button"
         style="text-align: center; padding: 10px;">
        <a class="join-study-button-button-link wp-block-button__link"
           href="{{ $s->getStudyLinks()->getStudyJoinUrl() }}">
            Join This Study
        </a>
    </div>

    <div id="study-charts-section">
        {!! $s->getOrSetCharts()->getHtmlWithDynamicCharts(false) !!}
    </div>

    <div id="study-text-section">
        {!! $s->getStudyHtml()->getStudyText() !!}
    </div>

    <div id="statistics-table-section">
        {!! $s->getStudyHtml()->getStatisticsTable() !!}
    </div>

    <div id="participant-instructions-section">
        {!! $s->getStudyHtml()->getStatisticsTable() !!}
    </div>

    <div id="study-sharing-section">
        @include('social-sharing-buttons', [
            'liStyle' =>  "display: inline;",
            'url'              => $s->getUrl(),
            'shortTitle'       => $s->getTitleAttribute(),
            'imagePreview'     => $s->getImage(),
            'briefDescription' => $s->getTagLine(),
            'emailUrl' => \App\Buttons\Sharing\EmailSharingButton::getEmailShareLink($s->getUrl(), $s->getTitleAttribute(), $s->getTagLine())
        ])
        {!! $s->getOrAddSocialSharingButtons() !!}
    </div>
</div>
