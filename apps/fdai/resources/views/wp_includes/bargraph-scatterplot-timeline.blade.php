@extends('layouts/default')
@section('title')
    Analyze
@parent
@stop
{{--Template Name: bargraph-gauge-scatterplot-timeline--}}
{{--Description: List of user_variable_relationships and relationship/longitudinal visualization--}}

<?php $pluginUrl = '/'.\App\Repos\QMWPPluginRepo::URL_PATH; ?>

@section('header_styles')
    <link href="{{ qm_asset($pluginUrl.'css/qmwp-bargraph-scatterplot-timeline.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ qm_asset($pluginUrl.'css/_shared_styles.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ qm_asset($pluginUrl.'css/jquery-ui-flick.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ qm_asset($pluginUrl.'css/jquery.dropdown.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ qm_asset($pluginUrl.'css/simpletip.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ qm_asset($pluginUrl.'css/jquery.datetimepicker.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ qm_asset($pluginUrl.'js/libs/fancybox/jquery.fancybox.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ qm_asset($pluginUrl.'css/sweetalert.css') }}" rel="stylesheet" type="text/css"/>
@stop


@section('content')


    @include('wp_includes/modules/add-measurement')
    @include('wp_includes/modules/delete-measurements')
    @include('wp_includes/modules/share')
    @include('wp_includes/modules/variable-settings')
<div id="content">
    <section id="section-configure">
        <div class="outstate">
            <div class="accordion-header" id="accordion-output-header">
                <div class="generalHeader resolutionHeader">
                    Examined Variable
                </div>
                <div class="icon-question-sign icon-large questionMark questionMarkAlone"
                     title="This is the variable to be examined. It can be considered to be a hypothetical cause for the variables in the bar graph or hypothetical effect of the variables in the bar graph by changing the setting below."></div>
            </div>
            <div class="accordion-content closed" id="accordion-output-content">
                <div class="inner">
                    <select id="selectOutputCategory"></select>
                    <!--<select id="selectOutputVariable"></select>-->
                    <input type="text" id="selectOutputVariable">
                    <select id="selectOutputAsType">
                        <option value="effect">List Predictors</option>
                        <option value="cause">List Outcomes</option>
                    </select>
                    <button id="button-output-varsettings">Settings</button>
                </div>
            </div>
        </div>

        <div id="bar-graph">
            <header class="graph-header" id="bar-graph-header">
                <div class="generalHeader bargraphHeader">
                    Please wait...
                </div>
                <div class="icon-question-sign icon-large questionMark"
                     title="This is the list of variables in order of their correlation with your examined variable."></div>
            </header>
            <div class="graph-content" style="height: 596px; overflow-y: scroll;">
                <img src="https://i.imgur.com/73BFcje.gif" class="barloading"
                     style="margin-left: 4%; margin-top: 20%; display:none"/>
                <span class="no-data" style="display:none"> <br/>  <center><h2>Hi!</h2>

                        <h2>We don't have enough data to determine your top predictors and outcomes. &nbsp;:(</h2>

                        <h2>Please check out the <a href="/getting-started" target="_blank">Getting Started</a> page to
                            see how to add more data!</h2></center><br/><br/></span>

                <div id="graph-bar" class="graph-content">
                </div>
                <input type="hidden" id="selectBargraphInputVariable" value=""/>
                <input type="hidden" id="selectBargraphInputCategory" value=""/>

            </div>
        </div>
    </section>

    <section id="section-analyze">

        <!-- <div class="inoutstate">
          <div class="daterange">
                            <div class="accordion-header" id="accordion-date-header">
                                    <div class="generalHeader resolutionHeader">
                                            Resolution
                                    </div>
                                    <div class="icon-question-sign icon-large questionMark questionMarkAlone"></div>
                            </div>
                            <div class="accordion-content closed" id="accordion-date-content">
                                    <div class="inner">
                                            <div id="accordion-content-rangepickers">
                                                    <input type="radio" value="Hour" id="radio3" name="radio" /><label for="radio3">Hour</label>
                                                    <input type="radio" value="Day" id="radio4" name="radio" checked='checked' /><label for="radio4">Day</label>
                                                    <input type="radio" value="Week" id="radio5" name="radio" /><label for="radio5">Week</label>
                                                    <input type="radio" value="Month" id="radio6" name="radio" /><label for="radio6">Month</label>
                                            </div>
                                    </div>
                            </div
            </div>


          </div>-->
        <div class="open" id="correlation-gauge" style="float:left">
            <div class="inner">
                <header class="graph-header" id="correlation-gauge-header">
                    <div class="generalHeader correlationHeader">
                        Scatterplot
                    </div>
                    <!--  <div id="gauge-correlation-settingsicon" data-dropdown="#dropdown-gauge-settings" class="gear-icon"></div>
                      <div class="icon-question-sign icon-large questionMark"></div> -->
                </header>
                <div class="graph-content" style="width: 100%; overflow: hidden;">
                    <div
                            style="float: right; width: 155px; padding-left: 12px; position:relative; height:100%; border-left: solid thin #F5FBFB;"
                            id="gauge-correlation"></div>
                    <div style="overflow: hidden; height: 100%;">
                        <table style="height: 100%;">
                            <tr>
                                <td>
                                    <strong>Statistical Relationship</strong>
                                </td>
                                <td id="statisticalRelationshipValue">
                                    Significant
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>Effect Size</strong>
                                </td>
                                <td id="effectSizeValue">
                                    Large
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="open" id="scatterplot-graph">
            <div class="inner">
                <header class="graph-header" id="scatterplot-graph-header">
                    <!-- <div class="generalHeader scatterplotHeader">
                         Scatterplot
                     </div>-->
                    <div class="keepInline">
                        <div id="graph-scatterplot-settingsicon" data-dropdown="#dropdown-scatterplot-settings"
                             class="gear-icon"></div>
                        <div class="icon-plus-sign icon-2x plusMark" title="Click to add a measurement."></div>
                        <div class="icon-question-sign icon-large questionMark questionMarkPlus"
                             title="Displays the collection of measurement points, each having the value of examined variable on the horizontal axis and the value of the other variable on the vertical axis."></div>
                    </div>
                </header>

                <div class="graph-content" id="graph-scatterplot"></div>

            </div>
        </div>
        <div id="timeline-graph">
            <header class="graph-header" id="timeline-graph-header">
                <div class="generalHeader timelineHeader">
                    Timeline
                </div>
                <!--<div id="gauge-timeline-settingsicon" data-dropdown="#dropdown-timeline-settings"
                     class="gear-icon"></div>-->
                <div class="icon-plus-sign icon-2x plusMark" title="Click to add a measurement."></div>
                <div class="icon-question-sign icon-large questionMark questionMarkPlus"
                     title="Shows the measurement data in the order of measurement dates."></div>
            </header>
            <div class="graph-content" id="graph-timeline"></div>
        </div>

    </section>
</div>

<!-- Menu for correlation gauge settings -->
<div id="dropdown-gauge-settings" class="dropdown dropdown-tip">
    <ul class="dropdown-menu">
        <li><a id="shareCorrelationGauge">Share graph</a></li>
    </ul>
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

<!-- Menu for timeline settings -->
<div id="dropdown-scatterplot-settings" class="dropdown dropdown-tip dropdown-anchor-right">
    <ul class="dropdown-menu">
        <li><label><input name="sp-show-linear-regression" type="checkbox"/> Show linear regression</label></li>
        <li class="dropdown-divider"></li>
        <li><a id="shareScatterplot">Share graph</a></li>
    </ul>
</div>

<!-- Menu for barchart settings -->
<div id="dropdown-barchart-settings" class="dropdown dropdown-tip dropdown-anchor-right">
    <ul class="dropdown-menu">
        <li><a id="" onclick="sortByCorrelation()">Sort By UserVariableRelationship</a></li>
        <li><a id="shareScatterplot" onclick="sortByCausality()">Sort By Causality Factor</a></li>
        <li style="padding:3px 15px;"><input type="text" id="minimumNumberOfSamples"
                                             placeholder="Min. Number of Samples"></li>
    </ul>
</div>


<div id="please-wait">
    <div id="please-wait-overlay">&nbsp;</div>
    <div class="please-wait-content">
        <img src="{{ $pluginUrl . 'css/images/ajax-loader.gif' }}" alt="">
        <span>please wait...</span>
    </div>
</div>
@stop

@section('footer_scripts')
{{-- page level scripts --}}
<script>
    accessToken = "";
    apiHost = "";
    qmwpShortCodeDefinedVariable = 'Overall Mood';
    qmwpShortCodeDefinedVariableAs = 'effect';
    qmwpShowVariableSelectors = 'true';
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
<script type='text/javascript' src='{{ $pluginUrl }}js/qmwp-bargraph-gauge-scatterplot-timeline_charts.js'></script>
<script type='text/javascript' src='{{ $pluginUrl }}js/_other_shared.js'></script>
<script type='text/javascript' src='{{ $pluginUrl }}js/_variable_settings.js'></script>
<script type='text/javascript' src='{{ $pluginUrl }}js/_data_refresh.js'></script>
<script type='text/javascript' src='{{ $pluginUrl }}js/qmwp-bargraph-gauge-scatterplot-timeline.js'></script>
@include('sweetalert::alert')
@stop
