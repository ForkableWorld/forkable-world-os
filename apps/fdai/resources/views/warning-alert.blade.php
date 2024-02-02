<!-- https://tailwindesign.com/components/button -->
<div
    class="relative flex justify-between bg-white rounded overflow-hidden p-2 space-x-1"
>
    <div
        class="absolute inset-0 border-l-4 border-red-400"
    ></div>
    <div class="flex items-baseline">
                    <span class="bg-red-300 bg-opacity-50 rounded-full p-1">
                      <svg
                          class="h-6 w-auto text-red-400"
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
    <div class="flex flex-grow items-center">
        <p class="leading-tight text-lg md:text-sm">
            <strong class="text-red-400">{{ $message }}</strong>
        </p>
    </div>
    <div class="z-10">
        <button
            type="button"
            class="bg-indigo-300 bg-opacity-25 text-gray-700 rounded overflow-hidden p-1 lg:p-2 focus:outline-none"
        >
            <svg
                class="h-4 w-auto"
                fill="currentColor"
                viewBox="0 0 20 20"
            >
                <path
                    fill-rule="evenodd"
                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                    clip-rule="evenodd"
                ></path>
            </svg>
        </button>
    </div>
</div>
