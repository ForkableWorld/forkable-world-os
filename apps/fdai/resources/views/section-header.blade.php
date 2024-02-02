<div class="flex flex-wrap py-1">
    <div class="w-full px-1">
        <nav class="relative flex flex-wrap items-center justify-between py-3 navbar-expand-lg bg-pink-500 rounded">
            <div class="container mx-auto flex flex-wrap items-center justify-between">
                <div class="w-full relative flex justify-between lg:w-auto px-4 lg:static lg:block lg:justify-start">
                    <span class="text-xl font-bold leading-relaxed inline-block mr-4 whitespace-no-wrap uppercase text-white">
                        {{ $slot }}
                    </span>
                </div>
            </div>
        </nav>
    </div>
</div>
