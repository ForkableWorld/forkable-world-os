<?php /** @var \App\Menus\QMMenu $menu */ ?>
<div style="padding-top: 10px;">
	<div>
		<h3 class="page-taxonomies-title">
			{{$menu->getTitleAttribute()}}
		</h3>
	</div>
    @foreach($menu->getButtons() as $button)
        {!! $button->getChipMedium() !!}
    @endforeach
</div>
