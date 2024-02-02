<?php /** @var App\Models\BaseModel $obj */ ?>
@php($obj = $meta ?? $obj ?? $report ?? $model ?? $page ?? $post ?? $uv ?? $v ?? null)
<!-- Start resources/views/meta.blade.php-->
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $title ?? html_title($obj) }}</title>
<meta name="description" content="{{ html_meta_description($obj) }}">
<meta name="keywords" content="{{ html_meta_keywords($obj) }}">
<meta name="author" content="{{ html_meta_author($obj) }}">
{!! html_social_meta($obj) !!}
@isset($obj)
    <link rel="canonical" href="{{\App\Utils\UrlHelper::canonicalizeUrls($obj->getUrl())}}" />
@endisset
<meta name="shortcut icon" content="{{ app_icon() }}">
<link rel="icon" type="image/x-icon" href="{{ app_icon() }}">
<link rel="apple-touch-icon" sizes="76x76" href="{{ app_icon() }}">
<!-- Set default share picture after custom section pictures -->
{!! MetaTag::tag('image', default_sharing_image()) !!}
<!-- End resources/views/footer-js.blade.php -->
