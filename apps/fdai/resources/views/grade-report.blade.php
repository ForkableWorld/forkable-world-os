<!DOCTYPE html>
<?php /** @var \App\Reports\GradeReport $model */ ?>
@php($page = $v ?? null)
<html lang="en-US" class="no-js">
@include('so-simple/_includes/head')
<body class="layout--post">
@include('so-simple/_includes/skip-links')
@include('so-simple/_includes/navigation', ['buttons' => $model->getTopMenu()->getButtons()])
@include('so-simple/_includes/masthead-post')
<main id="main" class="main-content" aria-label="Content">
    <article class="h-entry">
        <div class="page-wrapper">
            <header class="page-header">
                <h1 id="page-title" class="page-title p-name">{{ $model->getTitleAttribute() }}</h1>
            </header>
            <div class="page-sidebar">
                @include('so-simple/_includes/toc')
                @include('profile-sharing-box', ['model' => $model->getDailyAverageQMUserVariable()])
                @include('side-menus')
            </div>
            <div class="page-content js-toc-content">
                <div class="e-content">
                    @include('grade-report-content')
                    @include('variable-content', ['v' => $model->getDailyAverageQMUserVariable()])
                </div>
                @include('so-simple/_includes/social-share')
                @include('so-simple/_includes/disqus-comments')
            </div>
        </div>
    </article>
</main>
@include('so-simple/_includes/footer')
@include('javascript-in-body')
@include('toc-script')
{{--@include('highcharts-js')--}}
</body>
</html>
