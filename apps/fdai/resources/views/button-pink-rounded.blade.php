<!-- Start resources/views/button-pink-rounded.blade.php -->
<?php /** @var \App\Buttons\QMButton $button */ ?>
<a
        href="{!! $button->getUrl() !!}"
        title="{{ $button->getTooltip() }}"
        target="{{ $button->getTarget() }}"
        class="bg-pink-500 text-white active:bg-pink-600 font-bold uppercase text-base px-3 py-2 rounded-full 
        shadow-md hover:shadow-lg outline-none focus:outline-none mr-1 mb-1"
        type="button"
        style="margin: 1rem; transition: all .15s ease;"
>
    {!! $button->getFontAwesomeHtml() !!}
    {{ $button->getTitleAttribute() }}
</a>
<!-- End resources/views/button-pink-rounded.blade.php -->
