<?php /** @var \App\Buttons\QMButton[] $buttons */ ?>

@if( count($buttons) )
    <div style="padding-top: 10px;">
		<h3 class="page-taxonomies-title">
			Quick Menu
		</h3>
        <h3 class="page-taxonomies-title">
	        Variables
        </h3>
        <ul class="page-taxonomies">
            @foreach($buttons as $b)
                <li class="page-taxonomy button button--secondary button--pill button--sm">
                    {!! $b->getLink() !!}
                </li>
            @endforeach
        </ul>
    </div>
@endif
