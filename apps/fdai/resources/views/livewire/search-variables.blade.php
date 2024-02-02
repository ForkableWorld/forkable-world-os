<?php /** @var App\Models\Variable $variable */ ?>

<div>
    <div class="card">
        <div class="card-header card-header-tabs card-header-info">
            <div id="search-input-group" class="input-group"
                 style="text-align: center; margin: auto; width: 100%">
                <div class="text-field-container">
                    <div style="text-align: center; margin: auto; width: 100%"
                         class="mdc-text-field text-field demo-text-field-outlined-shaped mdc-text-field--outlined mdc-text-field--with-leading-icon">
                        <i class="material-icons mdc-text-field__icon">search</i>
                        <input id="search-input" type="text" wire:model="searchTerm" class="mdc-text-field__input"
                               aria-describedby="text-field-outlined-shape-two-helper-text"
                               placeholder="Search for a variable...">
                        <div class="mdc-notched-outline mdc-notched-outline--upgraded">
                            <div class="mdc-notched-outline__leading"></div>
                            <div class="mdc-notched-outline__notch" style="">
                                <label class="mdc-floating-label"
                                       for="text-field-outlined-shape-two"
                                       style="">
                                </label>
                            </div>
                            <div class="mdc-notched-outline__trailing"></div>
                        </div>
                    </div>
                    <div class="mdc-text-field-helper-line">
                        <p class="mdc-text-field-helper-text mdc-text-field-helper-text--persistent mdc-text-field-helper-text--validation-msg"
                           id="text-field-outlined-shape-two-helper-text">
                        </p>
                    </div>

                </div>
            </div>
            <div id="loading-div" style="margin: auto; text-align: center;" wire:loading>
                {{--
                <div>Searching...</div>
                <div id="loader-progress-bar"
                     style="width: 100%"
                     class="mdl-progress mdl-js-progress mdl-progress__indeterminate"></div>
                 --}}
                <img alt="Trippy GIF - Find &amp; Share on GIPHY" class="n3VNCb"
                     src="https://media.giphy.com/media/eu9tClIVo7OgdrH8k9/giphy.gif" data-noaft="1"
                     jsname="HiaYvf" jsaction="load:XAeZkd;" style="
                                 width: 100px;
                                height: 100px;
                                margin: 0px;
                                border-radius: 50%;
                                ">
            </div>
        </div>

        <div class="card-body">
            <ul id="search-results-list" style="list-style-type:none">
                @foreach($variables as $variable)
                    <li wire:key="{{ $loop->index }}" id="search-results-list-item-{{ $loop->index }}" class="mdl-list__item" style="padding-bottom: 0;">
                        <a id="search-results-link-{{ $loop->index }}" href="{{$variable->getUrl()}}">
                            <span id="search-results-list-item-header-{{ $loop->index }}" class="mdl-list__item-primary-content">
            {{--                      <i id="search-results-list-item-icon" class="mdl-list__item-avatar {{ $variable->getFontAwesome() }}"></i>--}}
                                  <img id="search-results-list-item-image-{{ $loop->index }}"
                                       class="mdl-list__item-avatar"
                                       style="-o-object-fit: scale-down; object-fit: scale-down; border-radius: 0;
                                       max-width: 32px;
                                       background-color: transparent;"
                                       src="{{ $variable->getImage() }}" alt="{{ $variable->getTitleAttribute() }}">
                                  <span id="search-results-list-item-title-{{ $loop->index }}" style="font-size: 30px;">
                                      {{ $variable->getTitleAttribute() }}
                                  </span>
                            </span>
                        </a>
                        <span class="mdl-list__item-secondary-content" style="display: inline;">
                             {!! $variable->getDataLabNameDropDownButton("") !!}

                        </span>

                        @isadmin
                        <span class="mdl-list__item-secondary-content" style="display: inline;">
                        {!! $variable->getDataLabModelDropDownButton() !!}

                        </span>
                        @endisadmin

                    </li>
                    <p>
                        {!! $variable->getRelationshipLabels() !!}
                    </p>
                    <span class="mdl-list__item-sub-title">  {{ $variable->getSubtitleAttribute() }} </span>
                @endforeach
            </ul>
        </div>
    </div>
</div>
