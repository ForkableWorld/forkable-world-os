@if ($user = auth()->user())
    @foreach($socialProviders as $provider)
        @if ($user->isAttached($provider->slug))
            <a
                    href="{{ route('social.detach', [$provider->slug]) }}"
                    class="btn btn-lg btn-danger btn-block {{ $provider->slug }}">
                {{ $provider->label }}
            </a>
        @else
            <a
                    href="{{ route('social.auth', [$provider->slug]) }}"
                    class="btn btn-lg btn-success btn-block {{ $provider->slug }}">
                {{ $provider->label }}
            </a>
        @endif
    @endforeach
@endif
