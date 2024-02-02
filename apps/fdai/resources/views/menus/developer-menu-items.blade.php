<?php $prefix = 'api/v2/'; ?>

{{--                            <li> <a target="_blank" href="http://quantimo.do/developer-documentation/"> <i class="menu-icon fa fa-fw fa-book"></i> <span class="mm-text ">Documentation</span> </a> </li>--}}
<li class="{{ (Request::is($prefix.'apps') || Request::is($prefix.'apps/*') ? 'active' : '') }}"
    data-container="body"
    data-toggle="popover"
    data-placement="right"
    data-content="Create your own app and take advantage of QuantiModo's data aggregation, storage, and analytical capabilities"
    data-trigger="hover">
    <a href="{{ route('apps') }}"><i class="menu-icon fa fa-fw fa-mobile"></i>
        <span class="mm-text ">Your Apps</span></a>
</li>
<li {{ (Request::is($prefix.'account/apiExplorer') ? 'class=active' : '') }}>
    <a href="{{ route('account.apiExplorer') }}">
        <i class="menu-icon fa fa-fw fa-exchange"></i> <span class="mm-text ">API Explorer</span>
    </a>
</li>
{{--<li> <a target="_blank" href="http://github.com/quantimodo/QuantiModo-SDK-Android"> <i class="menu-icon fa fa-fw fa-android"></i> <span class="mm-text ">Android SDK</span> </a> </li>--}}
{{--<li> <a target="_blank" href="http://github.com/Abolitionist-Project/QuantiModo-WordPress-Plugin"> <i class="menu-icon fa fa-fw fa-wordpress"></i> <span class="mm-text ">WordPress Plugin</span> </a> </li>--}}
<li>
    <a target="_blank"
       href="http://github.com/quantimodo">
        <i class="menu-icon fa fa-fw fa-code-fork"></i>
        <span class="mm-text ">SDK's</span></a>
</li>
<li class="{{ Request::is('datalab/bshafferOauthClients*') ? 'active' : '' }}">
    <a href="{{ route('datalab.oAuthClients.index') }}"><i class="fa fa-edit"></i><span>Oauth Clients</span></a>
</li>

<li class="{{ Request::is('datalab/bshafferOauthAccessTokens*') ? 'active' : '' }}">
    <a href="{{ route('datalab.oAuthAccessTokens.index') }}"><i class="fa fa-edit"></i><span>Oauth Access Tokens</span></a>
</li>
