<div class="container pt-2 mx-auto" x-data="model">
    @include('search-input')
    <div class="mt-4 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <template x-for="item in model.{{$searchId ?? $table ?? "no-search-id-provided"}}" :key="item">
            <a
                :href="`${item.url}`"
                class="flex items-center shadow hover:bg-indigo-100 hover:shadow-lg hover:rounded transition duration-150 ease-in-out transform hover:scale-105 p-3"
            >
                <img
                    class="w-10 h-10 rounded-full mr-4"
                    style="object-fit: scale-down;"
                    :src="`${item.avatar}`"
                    alt="`${item.title}`"
                />
                <div class="text-sm">
                    <p
                        class="text-gray-900 leading-none"
                        x-text="item.title"
                    ></p>
                    <p
                        class="text-gray-600"
                        x-text="item.subtitle"
                    ></p>
                </div>
            </a>
        </template>
    </div>
</div>
@include('not-found-box')
