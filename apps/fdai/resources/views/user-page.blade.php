<!DOCTYPE html>
<?php /** @var \App\Traits\ModelTraits\UserTrait $model */ ?>
@php($page = $user ?? null)
<html lang="en-US" class="no-js">
@include('so-simple/_includes/head')
<body class="layout--post">
@include('so-simple/_includes/skip-links')
@include('so-simple/_includes/navigation', ['buttons' => \App\Menus\JournalMenu::buttons()])
@include('so-simple/_includes/masthead-post')
<main id="main" class="main-content" aria-label="Content">
    <article class="h-entry">
        <div class="page-image">
            <img src="{{ \App\UI\ImageUrls::FACTORS_SLIDE }}" class="entry-feature-image u-photo"
                 alt="{{ $model->getDisplayNameAttribute() }} Mega Study">
        </div>
        <div class="page-wrapper">
            <header class="page-header">
                <h1 id="page-title" class="page-title p-name">{{ $model->getDisplayNameAttribute() }} Studies</h1>
            </header>
            <div class="page-sidebar">
                @include('so-simple/_includes/toc')
{{--                @include('variable-profile')--}}
                @include('side-menus')
            </div>
            <div class="page-content js-toc-content">
                <div class="e-content">
                    @include('variables-index')
                    @include('chip-search', ['searchId'  => 'variables', 'placeholder' => "Search for a variable...", 'buttons' => $model->getUserVariables()]))
                </div>
                @if($model->getShareAllData())
                    @include('so-simple/_includes/social-share')
{{--                    @include('so-simple/_includes/disqus-comments')--}}
                @endif
            </div>
        </div>
    </article>
</main>
@include('so-simple/_includes/footer')
@include('so-simple/_includes/scripts')
</body>
</html>
