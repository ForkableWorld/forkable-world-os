<?php /** @var \App\Cards\QMCard $card */ ?>
<link href="https://fonts.googleapis.com/css?family=Roboto&display=swap" rel="stylesheet">
<style>
    .material-card {
        font-family: 'Roboto', sans-serif;
        background-color: #FFF;
        box-shadow: 0px 2px 1px -1px rgba(0, 0, 0, 0.2), 0px 1px 1px 0px rgba(0, 0, 0, 0.14), 0px 1px 3px 0px rgba(0,0,0,.12);
    }
</style>
<div class="max-w-sm rounded material-card bg-white">
    <img class="w-full rounded-t" src="{{ $card->getImage() }}">
    <div class="px-6 py-4">
        <div class="font-bold text-xl tracking-wide">{{ $card->getTitleAttribute() }}</div>
        <div class="text-gray-500 text-sm mb-3">{{ $card->getSubtitleAttribute() }}</div>
        <p class="text-gray-700 text-base">
            {!! $card->getCardBody() !!}
        </p>
    </div>
    <div class="mx-4 mt-2 mb-4">
        @foreach($card->getButtons() as $button)
        <a class="tracking-wider uppercase font-bold text-purple-700 hover:bg-purple-100 rounded p-2 inline-block"
           href="{{ $button->getUrl() }}">{{ $button->getTitleAttribute() }}</a>
        @endforeach
    </div>
</div>
