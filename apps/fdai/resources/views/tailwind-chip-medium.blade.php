<?php /** @var \App\Buttons\QMButton $button */ ?>
<a href="{{ $button->getUrl() }}"
   style="display: inline-block;"
   title="{{ $button->getTooltip() }}"
    data-search="{{ $button->getKeywordString() }}">
    <div class="flex justify-center items-center m-1 font-medium py-1 px-2 bg-white rounded-full text-purple-700 bg-purple-100 border border-purple-300 ">
        @if( $button->getImage() )
            <div slot="avatar">
                <div class="flex relative w-4 h-4 bg-orange-500 justify-center items-center m-1 mr-2 ml-0 my-0 text rounded-full">
                    <img class="rounded-full" alt="A" src="{{ $button->getImage() }}">
                </div>
            </div>
        @endif
        <div class="text font-normal leading-none max-w-full flex-initial">
            {{ $button->getTitleAttribute() }}
            @if($button->getBadgeText() !== null)
                <span style="font-size: 0.6rem;"
                    class="badge rounded-full px-1 py-1 text-center object-right-top bg-white border border-purple-300">
                    {{ $button->getBadgeText() }}
                </span>
            @endif
        </div>
    </div>
</a>
