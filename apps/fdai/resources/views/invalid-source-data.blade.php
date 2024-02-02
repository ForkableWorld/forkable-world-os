<?php /** @var \App\Traits\QMAnalyzableTrait $a */ ?>
@php
    /** @var \App\Traits\QMAnalyzableTrait $a */
    /** @var \App\Traits\IsEditable[] $invalid */
    $invalid = $a->getInvalidSourceData();
@endphp
<div id="invalid-source-data" 
     style="float: right;"
     class="py-4 px-8 bg-white shadow-lg rounded-lg">
    <div class="flex justify-center md:justify-end -mt-16">
        <img class="w-20 h-20 object-cover rounded-full border-2" src="{{ \App\UI\ImageUrls::ESSENTIAL_COLLECTION_WARNING }}"
             alt="Invalid Source Data">
    </div>
    @if(!$invalid)
        <h2 class="text-gray-800 text-3xl font-semibold"> No Source Data was Invalid</h2>
    @else
        <h2 class="text-gray-800 text-3xl font-semibold">The Following Invalid Source Data was Excluded</h2>
        <p>Click to edit or delete it.</p>
        <p>Or change the minimum or maximum allowed values in {!! $a->getVariableSettingsLink() !!}.</p>
        <div>
            <div class="mt-2 text-gray-600">
                @foreach($invalid as $one)
                    {!! $one->getEditButton()->getTailwindChipWithClose() !!}
                @endforeach
            </div>
        </div>
    @endif
</div>
