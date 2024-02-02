<?php /** @var \App\Models\Card $card
 * @noinspection PhpFullyQualifiedNameUsageInspection
 */ ?>

<div class="vlp-link-container vlp-template-default">
    <a href="{!! $card->link !!}"
       rel="nofollow"
       target="_blank"
       class="vlp-link"
       title="{{ $card->title }}">
    </a>
    <div class="vlp-link-image-container">
        <div class="vlp-link-image">
            <img src="{!! $card->image !!}" style="max-width: 150px; max-height: 150px" alt="{{ $card->title }}"/>
        </div>
    </div>
    <div class="vlp-link-text-container">
        <div class="vlp-link-title">
            {{ $card->title }}
        </div>
        <div class="vlp-link-summary">
            {!! $card->content !!}
        </div>
    </div>
</div>
