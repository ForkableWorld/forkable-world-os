<?php /** @var \App\Buttons\QMButton[] $buttons */ ?>
<div class="p-8">
    <div class="shadow-xl rounded-lg">
        @if(isset($backgroundImage))
            <div style="background-image: url('{{$backgroundImage}}')"
                 class="h-64 bg-gray-200 bg-cover bg-center rounded-t-lg flex items-center justify-center">
                @if(isset($imageText))
                    <p class="text-white font-bold text-4xl">{{$imageText}}</p>
                @endif
            </div>
        @endif

        <div class="bg-white rounded-b-lg px-8">
            @if(isset($avatarImage))
                <div class="relative">
                    <img class="right-0 w-16 h-16 rounded-full mr-4 shadow-lg absolute -mt-8"
                         src="{{$avatarImage}}" alt="Avatar of {{ $title }}">
                </div>
            @endif
            <div class="pt-8 pb-8">
                @if(isset($title))
                    <h1 class="text-2xl font-bold text-gray-700">{{ $title }}</h1>
                @endif
                @if(isset($subtitle))
                    <p class="text-sm text-gray-600">{{ $subtitle }}</p>
                @endif
                @if(isset($paragraph))
                    <p class="mt-6 text-gray-700">{{ $paragraph }}</p>
                @endif

                @if(isset($content))
                    {!! $content !!}
                @endif
	                
                @if(isset($buttons))
                    <div class="flex justify-around mt-2">
                        @foreach($buttons as $button)
                            {!! $button->getTailwindCenteredRoundOutlineWithIcon() !!}
                        @endforeach
                    </div>
                @endif

            </div>
        </div>
    </div>
