<?php /** @var \App\Buttons\QMButton[] $buttons */ ?>
<div id="{{$searchId ?? $table ?? "no-search-id-provided"}}-list" class="flex flex-wrap justify-center">
    @foreach( $buttons as $b )
        {!! $b->getChipMedium() !!}
    @endforeach
</div>
