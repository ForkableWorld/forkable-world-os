<div id="{{$searchId ?? $table ?? "no-search-id-provided"}}-api-results" class="flex flex-wrap justify-center"></div>
<div id="{{$searchId ?? $table ?? "no-search-id-provided"}}-not-found-box" class="p-2" style="display: {{ isset($hideNotFoundBox) ? "none" : "block" }};">
    <div class="bg-white rounded-lg shadow-xl">
        <div class="p-2">
            <div style="text-align: center; margin:auto;">
                <img
                        src="{{\App\UI\ImageUrls::PUZZLED_ROBOT}}"
                        style="text-align: center; margin:auto; max-height: 200px;"
                        alt="PUZZLED_ROBOT"
                >
            </div>
            <div class="mt-8 text-center">
                <div class="font-bold text-xl text-gray-700 mb-1">
                    Don't see what you're looking for?
                </div>
                <div>
                    <p class="text-gray-600">
                        Create a study and help us eradicate suffering!
                    </p>
                </div>
                <div style="padding: 50px;" id="not-found-buttons-section">
                    @if(isset($notFoundButtons))
                        @foreach($notFoundButtons as $notFoundButton)
                            {!! $notFoundButton->getPinkRoundedButton() !!}
                        @endforeach
                    @else
                        {!! \App\Buttons\States\OnboardingStateButton::instance()->getPinkRoundedButton() !!}
                    @endisset
                </div>
                @include('variable-category-chips')
            </div>
        </div>
    </div>
</div>
