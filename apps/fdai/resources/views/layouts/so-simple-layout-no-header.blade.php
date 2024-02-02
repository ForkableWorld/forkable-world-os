<?php /** @var \App\Buttons\QMButton[] $buttons */ ?>
<html lang="en-US" class="no-js">
@include('so-simple/_includes/head')
<body class="layout--default">
{{--@include('so-simple/_includes/skip-links')--}}
{{--@include('so-simple/_includes/navigation', ['buttons' => \App\Menus\JournalMenu::buttons()])--}}
{{--@include('so-simple/_includes/masthead-home')--}}
<main id="main" class="main-content" aria-label="Content">
    <article>
        <div class="page-wrapper">
            @yield('content')
        </div>
    </article>
</main>
{{--@include('so-simple/_includes/footer')--}}
@include('pace')
@include('loggers-js')
@include('toc-script')
</body>
</html>
