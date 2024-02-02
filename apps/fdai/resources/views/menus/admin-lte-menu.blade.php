@php
    if(!isset($activePage)){$activePage = route_name();}
@endphp
@include('menus.analyze-menu')
@include('menus.import-menu')
@include('menus.models-menu')
@include('menus.devops-menu')
@include('menus.database-menu')
@include('menus.design-menu')
@include('menus.account-menu')
@include('menus.developer-menu')<li class="{{ Request::is('datalab/unitCategories*') ? 'active' : '' }}">
    <a href="{{ route('datalab.unitCategories.index') }}"><i class="fa fa-edit"></i><span>Unit Categories</span></a>
</li>

