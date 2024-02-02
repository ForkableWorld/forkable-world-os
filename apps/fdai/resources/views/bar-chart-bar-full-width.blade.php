<!-- Start resources/views/full-width-bar.blade.php -->
<?php /** @var \App\Charts\BarChartButton $button */ ?>
<a
    href="{{ $button->getUrl() }}"
    target="_blank"
    title="{{ $button->getTooltip() }}"
>
    <span class="text-2xl">{{ $button->getTitleAttribute() }}</span>
    <div class="mt-2 bg-gray-600 rounded-full">
        <div
                class="mt-2 bg-purple-900 py-1 text-center rounded-full"
                style="width: {{ $button->getWidth() }}%;"
        >
            <div
                    class="text-white inline-block px-2 rounded-full"
                    style="text-align: right;"
            >
                {{ $button->getValueText() }}
            </div>
        </div>
    </div>
</a>
