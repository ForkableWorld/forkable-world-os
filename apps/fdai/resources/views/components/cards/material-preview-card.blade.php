<?php /** @var \App\Models\Card $card
 * @noinspection PhpFullyQualifiedNameUsageInspection
 */ ?>
<a href="{!! $card->link !!}" target="_blank">
    <div class="card-header card-header-danger">
        {{ $card->title }}
    </div>
    <div class="card" style="width: 20rem;">
        <img class="card-img-top"
             src="{!! $card->image !!}"
             alt="{!! $card->title !!}">
        <div class="card-body">
            <h6 class="card-subtitle mb-2 text-muted">
                {{ $card->title }}
            </h6>
            <p class="card-text">
                {{ $card->content }}
            </p>
        </div>
    </div>
</a>
