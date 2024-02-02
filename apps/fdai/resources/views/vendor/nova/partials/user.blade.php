<?php /** @var App\Models\User $user */ ?>
<dropdown-trigger class="h-9 flex items-center">
    @isset($user->email)
        <img
            src="{{ $user->getImage() }}"
            class="rounded-full w-8 h-8 mr-3"
         alt="Avatar"/>
    @endisset

    <span class="text-90">
        {{ $user->name ?? $user->email ?? __('Astral User') }}
    </span>
</dropdown-trigger>

<dropdown-menu slot="menu" width="200" direction="rtl">
    <ul class="list-reset">
        <li>
            <a href="{{ route('astral.logout') }}" class="block no-underline text-90 hover:bg-30 p-3">
                {{ __('Logout') }}
            </a>
        </li>
        <li>
            <a href="{{ $user->getUrl() }}" class="block no-underline text-90 hover:bg-30 p-3">
                {{ __('Profile') }}
            </a>
        </li>
    </ul>
</dropdown-menu>
