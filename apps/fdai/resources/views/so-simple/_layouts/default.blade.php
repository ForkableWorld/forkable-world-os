<!DOCTYPE html>
<?php /** @var \App\Pages\BasePage $page */ ?>
@php($page = $page ?? $post ?? $model ?? null)
<html lang="en-US" class="no-js">
  @include('so-simple/_includes/head')
  <body class="layout--default">
    @include('so-simple/_includes/skip-links')
    @include('so-simple/_includes/navigation', ['buttons' => $page->getTopMenu()->getButtons()])
    @include('so-simple/_includes/masthead-home')

    @yield('content')

    @include('so-simple/_includes/footer')
    @include('so-simple/_includes/scripts')
  </body>
</html>
