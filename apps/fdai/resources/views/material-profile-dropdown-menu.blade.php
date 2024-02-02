<li class="nav-item dropdown">
    <a class="nav-link" href="{{ qm_url("user/profile") }}" id="navbarDropdownProfile" data-toggle="dropdown"
       aria-haspopup="true" aria-expanded="false">
        <i class="material-icons">account_circle</i>
        <p class="d-lg-none d-md-block">
            {{ __('Account') }}
        </p>
    </a>
    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownProfile">
        <a class="dropdown-item" href="{{ qm_url("user/profile") }}">{{ __('Profile') }}</a>
        <a class="dropdown-item" href="{{ ionic_url("#/app/settings") }}">{{ __('Settings') }}</a>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item" href="{{ route('logout') }}"
           onclick="event.preventDefault();document.getElementById('logout-form').submit();">{{ __('Log out') }}
        </a>
    </div>
</li>