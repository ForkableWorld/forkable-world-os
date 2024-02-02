
<!-- https://www.fda.gov/food/food-labeling-nutrition/changes-nutrition-facts-label -->
<div class="p-1 border-2 border-black font-sans w-72">
    <div class="text-4xl font-extrabold leading-none">{{$title}}</div>
    <div class="leading-snug">{{$description}}</div>
    <div class="flex justify-between font-bold border-b-8 border-black">
        <div>Serving size</div><div>2/3 cup (55g)</div>
    </div>
    <div class="flex justify-between items-end font-extrabold">
        <div>
            <div class="font-bold">Amount per serving</div>
            <div class="text-4xl">Calories</div>
        </div>
        <div class="text-5xl">45</div>
    </div>
    <div class="border-t-4 border-black text-sm pb-1">
        <div class="text-right font-bold pt-1 pb-1">Change from Baseline*</div>
        <template x-for="item in model.predictors" :key="item">
            <a class="flex justify-between"
               style="border-bottom: 1px solid #a0aec0;"
               :href="`${item.url}`"
               :title="`${item.tooltip}`">
                <div>
                        <span class="font-bold"
                              style="overflow: hidden;text-overflow: ellipsis;"
                              x-text="item.title"></span>
                    <span></span>
                </div>
                <div class="font-bold" style="text-align: right;" x-text="item.badge_text"></div>
            </a>
        </template>
        <div class="border-t-4 border-black flex leading-none text-lg pt-2 pb-1">
            <div class="pr-1">*</div>
            <div>{{$description}}</div>
        </div>
    </div>
</div>
