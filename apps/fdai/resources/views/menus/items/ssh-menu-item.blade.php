<li class="nav-item{{ route_name() == 'ssh' ? ' active' : '' }}">
    <a class="nav-link" href="{{ route('ssh') }}">
        <i class="material-icons">computer</i>
        <span class="sidebar-normal"> {{ __('SSH') }} </span>
    </a>
</li>