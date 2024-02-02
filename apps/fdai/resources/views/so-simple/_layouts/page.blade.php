<?php /** @var \App\Studies\QMStudy $page */ ?>
@extends('so-simple._layouts.default')
<main id="main" class="main-content" aria-label="Content">
  <article>
    @include('so-simple/_includes/page-image')
    <div class="page-wrapper">
      <header class="page-header">
          <h1 id="page-title" class="page-title">{{ $page->getTitleAttribute() }}</h1>
      </header>
      <div class="page-content js-toc-content">
        {{ $page->getContent() }}
          @include('so-simple/_includes/social-share')
      </div>
    </div>
  </article>
</main>
