<ul class="navbar-nav">
    <li class="nav-item">
	    {{--          <a href="{{ qm_url('user/dashboard') }}" class="nav-link">
					<i class="material-icons">dashboard</i> {{ __('Dashboard') }}
				  </a>--}}
	    <a href="{{ ionic_url('studies') }}" class="nav-link">
		    <i class="material-icons">dashboard</i> {{ __('Dashboard') }}
	    </a>
    </li>
    @include('notifications-dropdown')
    @include('material-profile-dropdown-menu')
</ul>
