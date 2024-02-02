@extends('layouts/default')
@section('title')
    Timeline
@parent
@stop
{{--Template Name: Analyze Page (v2, no user_variable_relationships)--}}
{{--Description: Description: Page based on Wordpress site--}}

<?php $pluginUrl = '/'.\App\Repos\QMWPPluginRepo::URL_PATH; ?>

@section('header_styles')
    <link href="{{ qm_asset($pluginUrl.'css/qmwp-timeline.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ qm_asset($pluginUrl.'css/_shared_styles.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ qm_asset($pluginUrl.'css/jquery-ui-flick.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ qm_asset($pluginUrl.'css/jquery.dropdown.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ qm_asset($pluginUrl.'js/libs/fancybox/jquery.fancybox.css') }}" rel="stylesheet" type="text/css"/>
@stop


@section('content')
    @include('wp_includes/modules/delete-measurements')
    @include('wp_includes/modules/share')
    @include('wp_includes/modules/variable-settings')
    <div id="content">
        <section id="section-configure">
            <div id="section-configure-input" class="open">
                <div class="inner">

                    <!--<div class="card-header accordion-header" id="accordion-date-header">
                        <div style="float: left; line-height: 42px;">
                            Date range
                        </div>
                    </div>
                    <div class="accordion-content closed" id="accordion-date-content">
                        <div class="inner">
                            <div id="accordion-content-rangepickers">
                                <input type="radio" value="Hour" id="radio3" name="radio"/><label for="radio3">Hour</label>
                                <input type="radio" value="Day" id="radio4" name="radio" checked='checked'/><label
                                    for="radio4">Day</label>
                                <input type="radio" value="Week" id="radio5" name="radio"/><label for="radio5">Week</label>
                                <input type="radio" value="Month" id="radio6" name="radio"/><label
                                    for="radio6">Month</label>
                            </div>
                        </div>
                    </div>-->

                    <div class="card-header accordion-header" id="accordion-input-header">
                        <div style="float: left; line-height: 42px;">
                            Variables
                        </div>
                    </div>
                    <div class="accordion-content closed" id="accordion-input-content" style="overflow: visible;">
                        <div class="inner">
                            <!--<ul id="addVariableMenu">
                                <li>
                                    <a>Add a Reminder</a>
                                    <ul id="addVariableMenuCategories" style="z-index: 999999">
                                    </ul>
                                </li>
                            </ul>-->
                            <input id="variable-selector" type="text" placeholder="Start typing variable name..."/>
                            <ul id="selectedVariables"></ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="section-analyze">
            <div style="width: 1px; overflow: hidden;"></div>
            <!-- Dirty hack for <768px -->

            <div id="timeline-graph">
                <header class="card-header graph-header">
                    <div style="float: left; line-height: 42px;">
                        Timeline
                    </div>
                </header>
                <div class="graph-content" id="graph-timeline">
                </div>
            </div>
        </section>
    </div>

    <!-- Menu for timeline settings -->
    <div id="dropdown-timeline-settings" class="dropdown dropdown-tip dropdown-anchor-right">
        <ul class="dropdown-menu">
            <li><label><input name="tl-enable-markers" type="checkbox"/> Show markers</label></li>
            <li><label><input name="tl-smooth-graph" type="checkbox"/> Smoothen graph</label></li>
            <li><label><input name="tl-enable-horizontal-guides" type="checkbox"/> Show horizontal guides</label></li>
            <li class="dropdown-divider"></li>
            <li><a id="shareTimeline">Share graph</a></li>
        </ul>
    </div>
@stop

@section('footer_scripts')
{{-- page level scripts --}}
<script>
    accessToken = "";
    apiHost = "";
    qmwpShortCodeDefinedVariables = 'Overall Mood';
</script>
<script type='text/javascript' src='{{ $pluginUrl }}js/libs/jquery.dropdown.min.js'></script>
<script type='text/javascript' src='{{ $pluginUrl }}js/libs/jquery.datetimepicker.js'></script>
<script type='text/javascript' src='{{ $pluginUrl }}js/libs/jquery.ui.touch-punch.min.js'></script>
<script type='text/javascript' src='{{ $pluginUrl }}js/libs/fancybox/jquery.fancybox.pack.js'></script>
<script type='text/javascript' src='{{ $pluginUrl }}js/math.js'></script>
<script type='text/javascript' src='{{ $pluginUrl }}js/jstz.min.js'></script>
<script type='text/javascript' src='{{ $pluginUrl }}js/libs/moment.min.js'></script>
<script type='text/javascript' src='{{ $pluginUrl }}js/libs/QuantiModo-SDK-JavaScript/quantimodo-api.js'></script>
<script type='text/javascript' src='{{ $pluginUrl }}js/libs/jquery.simpletip-1.3.1.js'></script>
<script type='text/javascript' src='{{ $pluginUrl }}js/libs/highstock.js'></script>
<script type='text/javascript' src='{{ $pluginUrl }}js/libs/highcharts-more.js'></script>
<script type='text/javascript' src='{{ $pluginUrl }}js/highcharts-fix.js'></script>
<script type='text/javascript' src='{{ $pluginUrl }}js/libs/exporting.js'></script>
<script type='text/javascript' src='{{ $pluginUrl }}js/libs/canvas-tools.js'></script>
<script type='text/javascript' src='{{ $pluginUrl }}js/libs/export-csv.js'></script>
<script type='text/javascript' src='{{ $pluginUrl }}js/libs/jspdf/jspdf.debug.js'></script>
<script type='text/javascript' src='{{ $pluginUrl }}js/libs/highcharts-export-clientside.js'></script>
<script type='text/javascript' src='{{ $pluginUrl }}js/_other_shared.js'></script>
<script type='text/javascript' src='{{ $pluginUrl }}js/_variable_settings.js'></script>
<script type='text/javascript' src='{{ $pluginUrl }}js/_data_refresh.js'></script>
<script type='text/javascript' src='{{ $pluginUrl }}js/timeline_charts.js'></script>
<script type='text/javascript' src='{{ $pluginUrl }}js/qmwp-timeline.js'></script>
@stop
