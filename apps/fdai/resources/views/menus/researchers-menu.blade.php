<?php $prefix = 'api/v2/'; ?>
<li class="{{ (Request::is($prefix.'studies') || Request::is($prefix.'studies/*') ? 'active' : '') }}"
    data-container="body"
    data-toggle="popover"
    data-placement="right"
    data-content="By creating a study, you'll receive a shareable authorization URL you can give to potential study participants.  Once a participant authorizes you to access their data, you'll be able to export it as a spreadsheet for analysis."
    data-trigger="hover">
    {{--<a href="{{ route('studies') }}"> <i class="menu-icon fa fa-fw fa-book"></i> <span class="mm-text ">Studies</span> </a>--}}
    <a target="_blank"
       href="https://web.quantimo.do/#/app/study-creation">
        <i class="menu-icon fa fa-fw fa-book"></i>
        <span class="mm-text ">Studies</span></a>
</li>