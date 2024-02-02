<?php /** @var \App\Charts\BarChartButton $button */ ?>
<div style="width: {{ $button->getWidth() }}%">
    <a href="{{ $button->getUrl() }}" title="{{ $button->getTooltip() }}">
        <div class="flex justify-center items-center m-1 font-medium py-1 px-2 rounded-full"
             style="background-color: {{$button->getBackgroundColor()}}; color: white;">
            @if( $button->image )
                <div slot="avatar">
                    <div class="flex relative w-4 h-4 bg-orange-500 justify-center items-center m-1 mr-2 ml-0 my-0 text-lg rounded-full">
                        <img class="rounded-full" alt="A" src="{{ $button->getImage() }}">
                    </div>
                </div>
            @endif
            <div class="text-lg font-normal leading-none max-w-full flex-initial">
                {{ $button->getTitleAttribute() }}
                @if($button->badgeText)
                    <span style="font-size: 0.6rem;"
                          class="badge rounded-full px-1 py-1 text-center object-right-top bg-white border border-purple-300">
                    {{ $button->badgeText }}
                </span>
                @endif
                @if($button->badgeText)
                    <span style="font-size: 0.6rem;"
                          class="badge rounded-full px-1 py-1 text-center object-right-top bg-white border border-purple-300">
                    {{ $button->badgeText }}
                </span>
                @endif
            </div>
        </div>
    </a>
</div>
