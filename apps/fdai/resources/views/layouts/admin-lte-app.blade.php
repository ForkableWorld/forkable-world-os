<!DOCTYPE html>
<!--suppress UnterminatedStatementJS -->
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ MetaTag::get('title') }}</title>
{!! MetaTag::tag('description') !!}
{!! MetaTag::tag('image') !!}
{!! MetaTag::openGraph() !!}
{!! MetaTag::twitterCard() !!}
{{--Set default share picture after custom section pictures--}}
{!! MetaTag::tag('image', default_sharing_image()) !!}
<meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
<meta name="csrf-token" content="{{ qm_csrf_token() }}">
@if( env_is_testing() )
    <link rel="shortcut icon" href="{{ \App\UI\ImageUrls::PHPSTORM }}">
@else
    <link rel="shortcut icon" href="{{ qm_asset('img/admin-favicon.png') }}">
@endif
@include('css')
	<script src="https://code.jquery.com/jquery-3.1.1.min.js" type="text/javascript"></script>
	<script defer src="https://code.getmdl.io/1.3.0/material.min.js"></script>
	<script src="https://unpkg.com/material-components-web@v4.0.0/dist/material-components-web.min.js"></script>
{{--	@include('psychedelic-loader')--}}

	<!-- This makes the current user id available in javascript -->
	@if(!auth()->guest())
		<script>
			window.Laravel = <?php echo json_encode(['csrfToken' => qm_csrf_token(),]); ?>
		</script>
		<script>
			window.Laravel.userId = <?php echo auth()->user()->getAuthIdentifier(); ?>
		</script>
	@endif
</head>
@php( $useSearchModal = false )
<body class="hold-transition modern-skin-dark sidebar-mini">
    @if (!Auth::guest())
        @include('admin-lte')
    @else
        @include('guest-layout')
    @endif
    @if( $useSearchModal )
        @include('modal-popup')
    @endif
@include('javascript-in-body')
<!-- Don't put qm_api_asset('/js/app.js') in global javascript-in-body because it breaks material layout -->
<script src="{{ qm_api_asset('/js/app.js') }}" type="text/javascript"></script>
</body>
</html>
