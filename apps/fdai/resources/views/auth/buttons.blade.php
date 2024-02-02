@foreach($socialProviders as $provider)
    <a
            href="{{ route('social.auth', [$provider->slug]) }}"
            class="btn btn-lg btn-default btn-block {{ $provider->slug }}">
        {{ $provider->label }}
    </a>
@endforeach
