<!DOCTYPE html>
<!--suppress UnterminatedStatementJS -->
<html lang="en">
<head>
<!-- Start resources/views/html.blade.php -->
	@include('meta')
{!! MetaTag::openGraph() !!}
{!! MetaTag::twitterCard() !!}

{{--Set default share picture after custom section pictures--}}
{!! MetaTag::tag('image', default_sharing_image()) !!}
<meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
<meta name="csrf-token" content="{{ qm_csrf_token() }}">
<link rel="shortcut icon" href="{{ qm_asset('img/admin-favicon.png') }}">
@include('css')
<!-- Scripts -->
@include('javascript-in-head')
</head>
<body class="hold-transition modern-skin-dark sidebar-mini">
{!! $html !!}
@include('javascript-in-body')
<!-- Don't put qm_api_asset('/js/app.js') in global javascript-in-body because it breaks material layout -->
<script src="{{ qm_api_asset('/js/app.js') }}" type="text/javascript"></script>
<!-- End resources/views/html.blade.php -->
</body>
</html>
