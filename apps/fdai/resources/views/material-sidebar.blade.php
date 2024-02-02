<?php /** @var \App\Menus\QMMenu $sideMenu */ ?>
<!-- Start resources/views/material-sidebar.blade.php -->
<div class="sidebar" data-color="azure" data-background-color="white">
    <!--
        Tip 1: You can change the color of the sidebar using: data-color="purple | azure | green | orange | danger"
        Tip 2: you can also add an image using data-image tag
    -->
    <div class="logo">
        <a href="{{ home_page() }}" class="simple-text logo-normal">
            {{ app_display_name() }}
        </a>
    </div>
    <div class="sidebar-wrapper">
        <ul class="nav">
            @if( isset($sideMenu) )
                {!! $sideMenu->getMaterialMenu() !!}
            @else
		        {!! \App\Menus\QMMenu::generateMaterialMenu() !!}
                @isadmin
		        <li class="nav-item">ADMIN MENU</li>
                    @include('material-admin-menu')
		        <li class="nav-item">EXAMPLES MENU</li>
                    @include('material-examples-menu')
                @endisadmin
            @endif
{{--            <li class="nav-item active-pro{{ route_name() == 'upgrade' ? ' active' : '' }}">--}}
{{--                <a class="nav-link" href="https://web.quantimo.do/#/app/upgrade">--}}
{{--                    <i class="material-icons">unarchive</i>--}}
{{--                    <p>{{ __('Upgrade') }}</p>--}}
{{--                </a>--}}
{{--            </li>--}}
        </ul>
    </div>
</div>
<!-- End resources/views/material-sidebar.blade.php -->
