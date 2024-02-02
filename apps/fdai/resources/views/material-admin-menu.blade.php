<li class="nav-item {{ in_array(route_name(), ['slow-queries', 'user-management', 'ssh']) ? ' active' : '' }}">
    <a class="nav-link" data-toggle="collapse" href="#admin-collapsible" aria-expanded="true">
        <i class="material-icons">security</i>
        <p>{{ __('Admin') }}
            <b class="caret"></b>
        </p>
    </a>
    <div class="collapse show" id="admin-collapsible">
        <ul class="nav">
            <li class="nav-item{{ route_name() === 'user-management' ? ' active' : '' }}">
                <a class="nav-link" href="{{ route('user-management.index') }}">
                    <i class="material-icons">people</i>
                    <span class="sidebar-normal"> {{ __('User Management') }} </span>
                </a>
            </li>
        </ul>
    </div>
    <div class="collapse show" id="admin-collapsible">
        <ul class="nav">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('datalab.users.index') }}">
                    <i class="material-icons">people</i>
                    <span class="sidebar-normal"> AdminLTE </span>
                </a>
            </li>
        </ul>
    </div>
</li>