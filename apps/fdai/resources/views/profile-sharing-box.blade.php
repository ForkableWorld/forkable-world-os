<?php /** @var \App\Models\Variable $model */ ?>
@php($v = $v ?? $variable ?? $model ?? null)
<div class="page-author h-card p-author">
    <img src="{{ $model->getImage() }}" class="author-avatar u-photo" alt="{{ $model->getTitleAttribute() }}">
  <div class="author-info">
      <div class="author-name">
          <span class="p-name">{{ $model->getTitleAttribute() }}</span>
      </div>
      <ul class="author-links">
        @foreach( $model->getSharingButtons() as $button)
          <li class="author-link">
            <a class="u-url" rel="me" href="{{ $button->getUrl() }}">
                <i class="{{ $button->getFontAwesome() }} fa-lg" title="{{ $button->getTitleAttribute() }}"></i>
            </a>
          </li>
        @endforeach
      </ul>
  </div>
</div>
