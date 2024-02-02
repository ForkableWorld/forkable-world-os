<?php $prefix = 'api/v2/'; ?>
<li {{ (Request::is($prefix.'account/subscription') ? 'class=active' : '') }}>
    <a href="{{ route('account.subscription') }}">
        <i class="menu-icon fa fa-fw fa-credit-card"></i> <span class="mm-text ">Subscription</span>
    </a>
</li>
<li {{ (Request::is($prefix.'account/password') ? 'class=active' : '') }}>
    <a href="{{ route('account.password') }}"><i class="menu-icon fa fa-fw fa-key"></i>
        <span class="mm-text ">Change Password</span></a>
</li>
<li {{ (Request::is($prefix.'account/edit') ? 'class=active' : '') }}>
    <a href="{{ route('account.edit') }}">
        <i class="menu-icon fa fa-fw fa-pencil-square-o"></i> <span class="mm-text ">Edit Your Profile</span>
    </a>
</li>
<li {{ (Request::is($prefix.'account/authorized-apps') ? 'class=active' : '') }} data-container="body"
    data-toggle="popover"
    data-placement="right"
    data-content="Manage which applications or studies are allowed to access your {{app_display_name()}} data"
    data-trigger="hover">
    <a href="{{ route('account.authorized.apps') }}">
        <i class="menu-icon fa fa-fw fa-lock"></i>
        <span class="mm-text ">Data Sharing</span></a>
</li>
<li {{ (Request::is($prefix.'account/export-data') ? 'class=active' : '') }}>
    <a href="{{ route('account.export.data') }}">
        <i class="menu-icon fa fa-fw fa-download"></i> <span class="mm-text ">Export Your Data</span>
    </a>
</li>