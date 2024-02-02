<?php /** @var \App\Studies\StudySection $section */ ?>

<div id="{{$section->id}}-study-section" 
     style="float: right;"
     class="study-section py-2 px-8 bg-white shadow-lg rounded-lg my-10">
    <div class="flex justify-center md:justify-end -mt-16 float-right">
        <img class="w-20 h-20 object-cover rounded-full border-2" src="{{ $section->image }}"
             alt="{{ $section->title }}">
    </div>
    <div>
        <h2 id="{{$section->id}}-heading"
            class="text-gray-800 text-3xl font-semibold">
            {{ $section->title }}
        </h2>
        <div class="mt-2 text-gray-600">
            {!! $section->body !!}
        </div>
    </div>
    @foreach( $section->getButtons() as $button )
        <div class="flex justify-end mt-4">
            <a href="{{ $button->getUrl() }}" class="text-xl font-medium text-indigo-500">{{ $button->getTitleAttribute() }}</a>
        </div>
    @endforeach
</div>
