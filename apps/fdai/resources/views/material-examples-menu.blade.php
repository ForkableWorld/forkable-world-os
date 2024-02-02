<li class="nav-item {{ in_array(route_name(),  ['table', 'typography', 'icons', 'map', 'notifications', 'language', 'table',]) ? ' active' : '' }}">
    <a class="nav-link" data-toggle="collapse" href="#examples-collapsible" aria-expanded="true">
        <i class="material-icons">extension</i>
        <p>{{ __('Examples') }}
            <b class="caret"></b>
        </p>
    </a>
    <div class="collapse" id="examples-collapsible">
        <ul class="nav">
            <li class="nav-item{{ route_name() == 'example-dashboard' ? ' active' : '' }}">
	            {{--          <a href="{{ qm_url('user/dashboard') }}" class="nav-link">
							<i class="material-icons">dashboard</i> {{ __('Dashboard') }}
						  </a>--}}
	            <a href="{{ ionic_url('studies') }}" class="nav-link">
		            <i class="material-icons">dashboard</i> {{ __('Dashboard') }}
	            </a>
            </li>
            <li class="nav-item{{ route_name() == 'table' ? ' active' : '' }}">
                <a class="nav-link" href="{{ route('table') }}">
                    <i class="material-icons">content_paste</i>
                    <span class="sidebar-normal"> {{ __('Table List') }} </span>
                </a>
            </li>
            <li class="nav-item{{ route_name() == 'typography' ? ' active' : '' }}">
                <a class="nav-link" href="{{ route('typography') }}">
                    <i class="material-icons">library_books</i>
                    <span class="sidebar-normal"> {{ __('Typography') }} </span>
                </a>
            </li>
            <li class="nav-item{{ route_name() == 'icons' ? ' active' : '' }}">
                <a class="nav-link" href="{{ route('icons') }}">
                    <i class="material-icons">bubble_chart</i>
                    <span class="sidebar-normal"> {{ __('Icons') }} </span>
                </a>
            </li>
            <li class="nav-item{{ route_name() == 'map' ? ' active' : '' }}">
                <a class="nav-link" href="{{ route('map') }}">
                    <i class="material-icons">location_ons</i>
                    <span class="sidebar-normal"> {{ __('Maps') }} </span>
                </a>
            </li>
            <li class="nav-item{{ route_name() == 'notifications' ? ' active' : '' }}">
                <a class="nav-link" href="{{ route('notifications') }}">
                    <i class="material-icons">notifications</i>
                    <span class="sidebar-normal"> {{ __('Notifications') }} </span>
                </a>
            </li>
            <li class="nav-item{{ route_name() == 'language' ? ' active' : '' }}">
                <a class="nav-link" href="{{ route('language') }}">
                    <i class="material-icons">language</i>
                    <span class="sidebar-normal"> {{ __('RTL') }} </span>
                </a>
            </li>
        </ul>
    </div>
</li>
