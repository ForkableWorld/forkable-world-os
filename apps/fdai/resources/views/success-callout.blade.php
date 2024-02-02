<?php /** @var \App\Cards\QMCard $card */ ?>
<!-- https://tailwindesign.com/components/callout -->
<div
    class="flex flex-wrap sm:flex-no-wrap justify-between bg-white rounded overflow-hidden p-2 space-x-0 sm:space-x-2"
>
    <div
        class="flex flex-1 sm:flex-initial justify-center items-baseline py-4 sm:py-0"
    >
                    <span class="bg-green-300 bg-opacity-50 rounded-full p-1">
                      <svg
                          class="h-10 sm:h-6 w-auto text-green-400"
                          fill="currentColor"
                          viewBox="0 0 20 20"
                      >
                        <path
                            fill-rule="evenodd"
                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                            clip-rule="evenodd"
                        ></path>
                      </svg>
                    </span>
    </div>
    <div
        class="flex flex-col flex-grow text-center sm:text-left"
    >
        <h1 class="font-medium leading-relaxed sm:leading-normal">
            <strong class="text-green-400">{{ $card->getTitleAttribute() }}</strong>
        </h1>
        <p class="leading-tight text-lg md:text-sm">
            {{ $card->getContent() }}
        </p>
    </div>
</div>
