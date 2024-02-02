<?php /** @var \App\Models\VariableCategory $model */ ?>
<html lang="en-US" class="no-js">
@include('so-simple/_includes/head')
<body class="layout--default">
@include('so-simple/_includes/skip-links')
@include('so-simple/_includes/navigation', ['buttons' => $model->getTopMenu()->getButtons()])
@include('so-simple/_includes/masthead-home')
<main id="main" class="main-content" aria-label="Content">
    <article>
        <div class="page-wrapper">
            <header class="page-header" style="display: none;">
                <h1 id="page-title" class="page-title">{{ $model->getTitleAttribute() }}</h1>
            </header>
            <div class="page-content js-toc-content">
                @include('variable-category-content')
            </div>
        </div>
    </article>
</main>
@include('so-simple/_includes/footer')
@include('pace')
@include('loggers-js')
@include('toc-script')
</body>
</html>
