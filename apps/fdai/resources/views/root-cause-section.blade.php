<!DOCTYPE html>
<?php /** @var \App\Reports\RootCauseAnalysisSection $obj */ ?>
@php($page = $s ?? null)
<html lang="en-US" class="no-js">
@include('so-simple/_includes/head')
<body class="layout--post">
@include('so-simple/_includes/skip-links')
@include('so-simple/_includes/navigation', ['buttons' => \App\Menus\JournalMenu::buttons()])
@include('so-simple/_includes/masthead-post')
<main id="main" class="main-content" aria-label="Content">
    <article class="h-entry">
        @include('so-simple/_includes/page-image')
        <div class="page-wrapper">
            <header class="page-header">
                <h1 id="page-title" class="page-title p-name">{{ $obj->getTitleAttribute() }}</h1>
            </header>
            <div class="page-sidebar">
                @include('so-simple/_includes/toc')
                @include('so-simple/_includes/page-author')
                @include('side-menus')
                @include('so-simple/_includes/page-tags')
            </div>
            <div class="page-content js-toc-content">
                <div class="e-content">
                    @include('root-cause-section-content')
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
@include('toc-script')
</body>
</html>
