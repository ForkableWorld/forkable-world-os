<?php /** @var App\Models\BaseModel $model */ ?>
<!DOCTYPE html>
<!--suppress UnterminatedStatementJS -->
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $model->getTitleAttribute() }}</title>
{!! MetaTag::tag('description', $model->getSubtitleAttribute()) !!}
{!! MetaTag::tag('image', $model->getImage()) !!}
{!! MetaTag::openGraph() !!}
{!! MetaTag::twitterCard() !!}
<meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
<link rel="shortcut icon" href="{{ $model->getImage() }}">
@include('css')
</head>
<body style="max-width: 600px; margin: auto; text-align: center;">
<h1>{{ $model->getTitleAttribute() }}</h1>
<h3>{{ $model->getSubtitleAttribute() }}</h3>
{!! $model->getImageHtml() !!}
@yield('content')
{!! $model->getInterestingRelationshipsMenu()->getMaterialStatCards() !!}
<footer class="footer">
    <div class="container-fluid">
        <nav>
            {!! \App\Menus\GeneralMenu::instance()->getHorizontalList() !!}
        </nav>
    </div>
</footer>
<footer class="main-footer" style="max-height: 100px;text-align: center; margin-left: 0;">
    <strong>Copyright © 2020 <a href="#">{{ app_display_name() }}</a>.</strong> All rights reserved.
</footer>
</body>
</html>
