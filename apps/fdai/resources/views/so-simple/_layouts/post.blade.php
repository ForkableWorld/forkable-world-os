<!DOCTYPE html>
<?php /** @var \App\Studies\QMStudy $page */ ?>
@php($page = $page ?? $post ?? $model ?? null)
<html lang="en-US" class="no-js">
@include('so-simple/_includes/head')
<body class="layout--post">
@include('so-simple/_includes/skip-links')
@include('so-simple/_includes/navigation', ['buttons' => $page->getTopMenu()->getButtons()])
@include('so-simple/_includes/masthead-post')
<main id="main" class="main-content" aria-label="Content">
    <article class="h-entry">
        @include('so-simple/_includes/page-image')
        <div class="page-wrapper">
            <header class="page-header">
                <h1 id="page-title" class="page-title p-name">{{ $page->getTitleAttribute() }}</h1>
            </header>

            <div class="page-sidebar">
                @include('so-simple/_includes/toc')
                @include('so-simple/_includes/page-author')
                @include('so-simple/_includes/page-categories')
                @include('so-simple/_includes/page-tags')
            </div>
            <div class="page-content js-toc-content">
                <div class="e-content">
                    @if(isset($content))
                        {!! $content !!}
                    @else
                        @yield('content')
                    @endif
                </div>
                @include('so-simple/_includes/social-share')
                @include('so-simple/_includes/disqus-comments')
                {{-- TODO:       @include('so-simple/_includes/page-pagination')--}}
            </div>
        </div>
    </article>
</main>
@include('so-simple/_includes/footer')
@include('so-simple/_includes/scripts')
</body>
</html>
