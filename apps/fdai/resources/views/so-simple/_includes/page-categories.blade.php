<?php /** @var \App\Traits\HasCategories $page */ ?>
@php($page = $page ?? $post ?? $model ?? null)

@if($page->getCategoryButtons())
    <div style="padding-top: 10px;">
	    <div>
		    <h3 class="page-taxonomies-title">
			    Categories
		    </h3>
	    </div>
        <ul class="page-taxonomies">
            @foreach($page->getCategoryButtons() as $button)
                <li class="page-taxonomy">
                    <a class="p-category button button--secondary button--pill button--sm"
                        href="{{ $button->getUrl() }}"
                        title="{{ $button->getTooltip() }}">
                        {{ $button->getTitleAttribute() }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
@endif
