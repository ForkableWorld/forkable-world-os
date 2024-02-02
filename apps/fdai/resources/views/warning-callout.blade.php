<?php /** @var \App\Cards\QMCard $card */ ?>
<!-- https://tailwindesign.com/components/callout -->
<div
    class="relative flex flex-wrap sm:flex-no-wrap justify-between bg-white rounded overflow-hidden p-2 space-x-0 sm:space-x-2"
>
    <div
        class="absolute inset-0 border-l-4 border-red-400"
    ></div>
    <div
        class="flex flex-1 sm:flex-initial justify-center items-baseline py-4 sm:py-0"
    >
                    <span class="bg-red-300 bg-opacity-50 rounded-full p-1">
                      <svg
                          class="h-10 sm:h-6 w-auto text-red-400"
                          fill="currentColor"
                          viewBox="0 0 20 20"
                      >
                        <path
                            fill-rule="evenodd"
                            d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944zM11 14a1 1 0 11-2 0 1 1 0 012 0zm0-7a1 1 0 10-2 0v3a1 1 0 102 0V7z"
                            clip-rule="evenodd"
                        ></path>
                      </svg>
                    </span>
    </div>
    <div
        class="flex flex-col flex-grow text-center sm:text-left"
    >
        <h1 class="font-medium leading-relaxed sm:leading-normal">
            <strong class="text-red-400">$card->getTitleAttribute()</strong>
        </h1>
        <p class="leading-tight text-lg md:text-sm">
            {{ $card->getContent() }}
        </p>
    </div>
</div>
