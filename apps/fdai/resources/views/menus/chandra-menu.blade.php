<?php $prefix = 'api/v2/'; ?>
<ul class="navigation">
    @if (empty(Cookie::get('physician')))
        <li class="treeview menu-dropdown {{ (Request::is($prefix.'account/password') ||
                    Request::is($prefix.'account/edit') || Request::is($prefix.'account/export-data') ? 'active' : '') }}">
            <a href="#"><i class="menu-icon fa fa-fw fa-user"></i>
                <span class="mm-text ">Your Account</span> <span class="fa arrow"></span></a>
            <ul class="sub-menu collapse">
                @include('menus.account-menu-items')
            </ul>
        </li>
        @if(app_display_name() === 'QuantiModo')
            <li {{ (Request::is($prefix.'account/connectors') ? 'class=active' : '') }}>
                <a href="{{ route('account.connectors') }}">
                    <i class="menu-icon fa fa-fw fa-exchange"></i>
                    <span class="mm-text ">Import Data</span></a>
            </li>
            <li class="menu-dropdown {{ (Request::is($prefix.'apps/*') || Request::is($prefix.'apps') ? 'active' : '') }}">
                <a href="#"><i class="menu-icon fa fa-fw fa-code"></i>
                    <span class="mm-text ">Developers</span> <span class="fa arrow"></span></a>
                <ul class="sub-menu collapse">
                    @include('menus.developer-menu-items')
                </ul>
            </li>
            <li class="menu-dropdown {{ (Request::is($prefix.'studies/*') || Request::is($prefix.'studies') ? 'active' : '') }}">
                <a href="#"><i class="menu-icon fa fa-fw fa-institution"></i> <span class="mm-text ">Researchers</span>
                    <span class="fa arrow"></span></a>
                <ul class="sub-menu collapse">
                    @include('menus.researchers-menu')
                </ul>
            </li>
        @endif
        <li {{ (Request::is($prefix.'physicians') || Request::is($prefix.'physicians/*') ? 'class=active' : '') }}>
            <a href="{{ route('physicians') }}"><i class="menu-icon fa fa-fw fa-stethoscope"></i>
                <span class="mm-text ">{{physicianAlias()}}s</span></a>
        </li>
    @endif
    <li>
        <a target="_blank"
           href="http://help.quantimo.do"><i class="menu-icon fa fa-fw fa-question"></i>
            <span class="mm-text ">Help & Feedback</span></a>
    </li>
    <li class="menu-dropdown {{ (Request::is($prefix.'apps/*') || Request::is($prefix.'apps') ? 'active' : '') }}">
        <a href="#"><i class="menu-icon fa fa-fw fa-code"></i>
            <span class="mm-text ">Platforms</span> <span class="fa arrow"></span></a>
        <ul class="sub-menu collapse">
            @include('menus.platforms-menu')
        </ul>
    </li>
    @if( Auth::user()->inRole('admin') )
        <li>
            <a target="_blank"
                href="{{ route('admin') }}">
                <i class="menu-icon fa fa-fw fa-shield"></i> <span class="mm-text ">Admin</span></a>
        </li>
    @endif
</ul>
<!-- / .navigation -->