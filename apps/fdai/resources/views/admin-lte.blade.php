<div class="wrapper">
    <!-- Main Header -->
    <header class="main-header">
        <!-- Logo -->
        <a href="{{url('datalab')}}" class="logo">
{{--            <b>{{ app_display_name() }}</b> --}}
            Data Lab
        </a>
        <!-- Header Navbar -->
        <nav class="navbar navbar-static-top" role="navigation">
            <!-- Sidebar toggle button-->
            <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                <span class="sr-only">Toggle navigation</span>
            </a>
            @include('navbar-menus')
        </nav>
    </header>
    <!-- Left side column. contains the logo and sidebar -->
@include('layouts.admin-lte-sidebar')
<!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        @include('flash::message')
        @yield('content')
    </div>
    @include('footer')
</div>