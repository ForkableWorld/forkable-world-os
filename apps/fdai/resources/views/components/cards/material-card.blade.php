<?php /** @var \App\Models\Card $card
 * @noinspection PhpFullyQualifiedNameUsageInspection
 */ ?>
<md-card>
    <md-card-header>
        @if($card->avatar)<md-card-avatar><img src="{{$card->avatar}}"/></md-card-avatar>@endif
        <md-card-header-text>
            @if($card->title)<span class="md-title">{{$card->title}}</span>@endif
            @if($card->subTitle)<span class="md-subhead">{{$card->subTitle}}</span>@endif
        </md-card-header-text>
    </md-card-header>
    @if($card->image)<img src="{{$card->image}}" class="md-card-image" alt="">@endif
    @if($card->htmlContent)<md-card-content><div>{!! $card->htmlContent !!}</div></md-card-content>@endif
    @if($card->buttons)
        <md-card-actions layout="column" layout-align="start">
            @foreach($card->buttons as $button)
                <md-button id="{{$button->text}}" aria-label="{{$button->text}}" href="{{$button->link}}">
                    <i class="{{$button->ionIcon}}"> &nbsp;{{$button->text}}</i>
                    @if($button->tooltip)<md-tooltip>{{$button->tooltip}}</md-tooltip>@endif
                </md-button>
            @endforeach
        </md-card-actions>
    @endif
    @if($card->sharingButtons)
        <md-card-actions layout="row" layout-align="end center">
            @foreach($card->sharingButtons as $button)
                <md-button id="{{$button->text}}" aria-label="{{$button->text}}" href="{{$button->link}}">
                    <i class="{{$button->ionIcon}}"></i>
                    <md-tooltip>{{$button->tooltip}}</md-tooltip>
                </md-button>
                &nbsp;
            @endforeach
        </md-card-actions>
    @endif
</md-card>