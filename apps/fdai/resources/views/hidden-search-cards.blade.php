<!-- Make sure to include the search box resources/views/navbar-card-filter-input.blade.php -->
@if(\App\Slim\Middleware\QMAuth::isAdmin())
    @include('admin-search-menu-material-cards-display-none')
@else
    @include('user-menu-material-cards-display-none')
@endif
{{-- Too Slow => {!! \App\Menus\SearchMenu::instance()->getMaterialStatCards('display: none;') !!}--}}