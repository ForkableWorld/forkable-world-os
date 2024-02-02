@component('components.cards.basic-card')
    @slot('title')
        {{ $title }}
    @endslot
    @slot('body')
        @include('components.buttons.rating-face-buttons')
    @endslot
    @slot('footer')
        {{ $footer }}
    @endslot
@endcomponent