
<!-- https://www.fda.gov/food/food-labeling-nutrition/changes-nutrition-facts-label -->
<h3 id="predictors-label"
    style="visibility: hidden;">
    Predictors Label
</h3>
<div class="container pt-2 mx-auto" x-data="model">
    <div class="p-1 border-2 border-black font-sans w-72"
         id="predictors-label-list"
         x-show="model.predictors.length"
         style="margin: auto" >
        <div class="text-3xl font-extrabold leading-none">Predictors</div>
        <div class="font-bold">of <span x-text="name"></span></div>
        <div class="leading-snug border-b-2 border-t-4 border-black pb-1">
            Below is the change in <span x-text="name"></span> after the listed predictor is higher than average.
        </div>
        <form onsubmit="return false;" style="margin-top: 0;" class="border-b-2 border-black">
            <div style="width: 100%; font-size: 0.9em;">
                <i class="fas fa-search" style="padding: 0.5rem"></i>
                <!--suppress HtmlFormInputWithoutLabel -->
                <input
                    id="predictors-label-input"
                    style="width: 80%"
                    onkeyup="searchFilter('predictors-label')"
                    placeholder="Filter by name..."
                    type="search"
                />
            </div>
        </form>
        <div style="margin: auto; text-align: center;">
            <div class="flex justify-center items-center m-1 font-medium py-1 px-2 bg-white rounded-full border border-gray-300"
                 style="cursor: pointer; display: inline-block">
                <div class="text-lg font-normal leading-none max-w-full flex-initial"
                     @click="predictors.sort((a, b) => (Math.abs(a['change']) < Math.abs(b['change'])) ? 1 : -1)"
                     title="Click to put the results with the largest observed change at the top"
                >
                    Sort by Effect Size
                </div>
            </div>
            <div class="flex justify-center items-center m-1 font-medium py-1 px-2 bg-white rounded-full border border-gray-300"
                 style="cursor: pointer;  display: inline-block;">
                <div class="text-lg font-normal leading-none max-w-full flex-initial"
                     @click="predictors.sort((a, b) => (a['number_of_days'] < b['number_of_days']) ? 1 : -1)"
                     title="Click to put the results with the most supporting data points at the top."
                >
                    Sort by Confidence
                </div>
            </div>
        </div>
        <div class="text-sm pb-1">
            <div class="text-right font-bold pt-1 pb-1"
                 @click="predictors.sort((a, b) => (Math.abs(a['change']) < Math.abs(b['change'])) ? 1 : -1)"
                 title="Click to put the results with the largest observed change at the top"
            >
                % Change from Baseline*
            </div>
            <template x-for="item in predictors" :key="item">
                <a
                    class="flex justify-between"
                    style="border-bottom: 1px solid #a0aec0;"
                    :href="`${item.url}`"
                    :title="`${item.tooltip}`"
                >
                    <div>
                        <span
                            class="font-bold"
                            style="overflow: hidden;text-overflow: ellipsis;"
                            x-text="item.title"
                        ></span>
{{--                        <span x-text="item.number_of_days"></span>--}}
                    </div>
                    <div class="font-bold" style="text-align: right;" x-text="item.badge_text"></div>
                </a>
            </template>
            <div class="border-t-4 border-black flex leading-none text-lg pt-2 pb-1">
                <div class="pr-1">*</div>
                <div>
                    Change from the average <span x-text="name"></span> seen after the predictor is higher than average.
                </div>
            </div>
        </div>
    </div>
</div>
