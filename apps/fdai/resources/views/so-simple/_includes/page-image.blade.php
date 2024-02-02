<?php /** @var \App\Studies\QMStudy $page */ ?>
@php($page = $page ?? $post ?? $model ?? null)
<div class="page-image">
    <img src="{{ $page->getImage() }}" class="entry-feature-image u-photo"
         alt="{{ $page->getTitleAttribute() }}">
</div>
