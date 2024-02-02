<div class="container pt-2 mx-auto" x-data="model">
    @include('search-input')
    <div id="{{$searchId ?? $table ?? "no-search-id-provided"}}-list"
         class="flex flex-wrap justify-center" >
        <template x-for="item in  model.{{$searchId ?? $table ?? "no-search-id-provided"}}" :key="item">
            <a :href="`${item.url}`"
               style="margin: 0.2rem;"
               :title="`${item.tooltip}`">
                <div class="flex justify-center items-center font-medium py-1 px-2 bg-white rounded-full text-purple-700 bg-purple-100 border border-purple-300 ">
                    <div slot="avatar">
                        <div class="flex relative w-4 h-4 bg-orange-500 justify-center items-center m-1 mr-2 ml-0 my-0 text-lg rounded-full">
                            {{-- Image is slow! <img class="rounded-full" alt="A" :src="`${item.avatar}`"> --}}
                            <i :class="`${item.font_awesome}`"></i>
                        </div>
                    </div>
                    <div class="text-lg font-normal leading-none max-w-full flex-initial" x-text="item.title">

                    </div>
                    <div style="font-size: 0.6rem; margin-left: 0.6rem;"
                         x-text="item.badge_text"
                         class="badge rounded-full px-1 py-1 text-center object-right-top bg-white border border-purple-300">
                    </div>
                </div>
            </a>
        </template>
    </div>
</div>
@include('not-found-box')
