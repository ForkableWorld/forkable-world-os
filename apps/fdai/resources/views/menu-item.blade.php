<li class="nav-item{{ route_name() === 'user-management' ? ' active' : '' }}">
    <a class="nav-link" href="{{ route('user-management.index') }}">
        <i class="material-icons">people</i>
        <span class="sidebar-normal"> {{ __('User Management') }} </span>
    </a>
</li>