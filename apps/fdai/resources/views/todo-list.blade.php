<?php /** @var \App\Cards\CardList $cardList */ ?>
<div class="h-100 w-full flex items-center justify-center bg-teal-lightest font-sans">
    <div class="bg-white rounded shadow p-6 m-4 w-full lg:w-3/4 lg:max-w-lg">
        <div class="mb-4">
            <h1 class="text-grey-darkest">{{ $cardList->getTitleAttribute() }}</h1>
        </div>
        <div>
            @foreach( $cardList->getCards() as $card )
                <div class="flex mb-4 items-center">
                    <p class="w-full text-grey-darkest">{{ $card->title }}</p>
                    @foreach( $card->getButtons() as $button )
                        <a href="{{ $button->getUrl() }}">
                            <button class="flex-no-shrink p-2 border-2 rounded text-teal border-teal hover:text-white hover:bg-teal">
                                {{ $button->getTitleAttribute() }}
                            </button>
                        </a>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
</div>
