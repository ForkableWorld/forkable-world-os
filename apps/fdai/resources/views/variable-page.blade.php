<!DOCTYPE html>
<?php /** @var \App\Models\Variable $model */ ?>
@php($page = $model ?? null)
<html lang="en-US" class="no-js">
@include('so-simple/_includes/head')
<body class="layout--post">
@include('so-simple/_includes/skip-links')
@include('so-simple/_includes/navigation', ['buttons' => $model->getTopMenu()->getButtons()])
@include('so-simple/_includes/masthead-post')
<main id="main" class="main-content" aria-label="Content">
    <article class="h-entry">
        <div class="page-image">
            <img src="{{ \App\UI\ImageUrls::FACTORS_SLIDE }}" class="entry-feature-image u-photo"
                 alt="{{ $model->getTitleAttribute() }} Mega Study">
        </div>
        <div class="page-wrapper">
            <header class="page-header">
                <h1 id="page-title" class="page-title p-name">{{ $model->getTitleAttribute() }} Mega Study</h1>
            </header>
            <div class="page-sidebar">
                @include('so-simple/_includes/toc')
                @include('profile-sharing-box', ['model' => $model])
                @include('side-menus')
                @include('so-simple/_includes/page-tags')
            </div>
            <div class="page-content js-toc-content">
                <div class="e-content">
                    @include('variable-content')
                </div>
                @include('so-simple/_includes/social-share')
                @include('so-simple/_includes/disqus-comments')
                {{-- TODO:       @include('so-simple/_includes/page-pagination')--}}
            </div>
        </div>
    </article>
</main>
@include('so-simple/_includes/footer')
@include('javascript-in-body')
@include('ionic_js')
resources/views/search-input.blade.php -->
@include('toc-script')
{{--@include('highcharts-js')--}}
</body>
</html>
