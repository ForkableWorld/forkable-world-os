<?php /** @var \App\Studies\QMStudy $page */ ?>
@php
$page = $page ?? $post ?? $model ?? null;
$user = method_exists($page, 'getQMUser') ? $page->getQMUser() : \App\Models\User::mike()
@endphp
<div class="page-author h-card p-author">
    <img src="{{ $user->getImage() }}" class="author-avatar u-photo" alt="{{ $user->getDisplayNameAttribute() }}">

  <div class="author-info">
      <div class="author-name">
          PRINCIPAL INVESTIGATOR
      </div>
      <div class="author-name">
          <span class="p-name">{{ $user->getDisplayNameAttribute() }}</span>
      </div>
    @if($user->getSocialButtons())
      <ul class="author-links">
        @foreach($user->getSocialButtons() as $button)
          <li class="author-link">
            <a class="u-url" rel="me" href="{{ $button->getUrl() }}">
                <i class="{{ $button->getFontAwesome() }} fa-lg" title="{{ $button->getTitleAttribute() }}"></i>
            </a>
          </li>
        @endforeach
      </ul>
    @endif
{{--    TODO
    @if($site.read_time )@include('so-simple/_includes/read-time')@endif
    @if($page.date )@include('so-simple/_includes/page-date')@endif
--}}
  </div>
</div>
