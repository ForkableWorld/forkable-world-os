<?php /** @var \App\Buttons\QMButton $button */ ?>
<a href="{!! $button->getUrl() !!}">
    <div class="lg:pt-12 pt-6 w-full md:w-4/12 px-4 text-center">
        <div class="relative flex flex-col min-w-0 break-words bg-white w-full shadow-lg rounded-lg">
            <div class="px-4 py-5 flex-auto">
                <div class="text-white p-3 text-center inline-flex items-center justify-center w-12 h-12 shadow-lg rounded-full"
                     style="{{$button->getColorGradientCss()}}"
                >
                    <i class="{{ $button->getFontAwesome() }}"></i>
                </div>
                <h6 class="text-xl font-semibold">{{ $button->getTitleAttribute() }}</h6>
{{--                <p class="mt-2 mb-4 text-gray-600">{{ $button->getTooltip() }}</p>--}}
            </div>
        </div>
    </div>
</a>
