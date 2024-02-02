<?php /** @var \App\Cards\QMCard $card */ ?>
<div class="card {{ $card->getCssClassesString() }}">
    <div class="card-body">
        <h5 class="card-title">{{ $card->getTitleAttribute() }}</h5>
        <div class="card-text">{!! $card->getContent() !!}</div>
        @foreach($card->getButtons() as $b){
        <a href="{{ $b->getUrl() }}" title='{{ $b->getTooltip() }}' onclick='showLoader()'>
            <div class="stats">
                <i class="material-icons">launch</i> {{ $b->getTitleAttribute() }}
            </div>
        </a>
        @endforeach
    </div>
</div>
