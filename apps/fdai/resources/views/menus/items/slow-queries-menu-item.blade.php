<li class="nav-item{{ route_name() == 'slow-queries' ? ' active' : '' }}">
    <a class="nav-link" href="{{ route('slow-queries') }}">
        <i class="material-icons">hourglass_empty</i>
        <span class="sidebar-normal"> {{ __('Slow Queries') }} </span>
    </a>
</li>