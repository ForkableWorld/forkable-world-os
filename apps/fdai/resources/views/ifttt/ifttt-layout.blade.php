<?php /** @noinspection PhpFullyQualifiedNameUsageInspection */
/** @var \App\Buttons\QMButton[] $buttons */
?>

<head>
	@include('fontawesome')
	@include('javascript-in-head')
	<style type="text/css">.turbolinks-progress-bar {
			position: fixed;
			display: block;
			top: 0;
			left: 0;
			height: 3px;
			background: #0076ff;
			z-index: 9999;
			transition: width 300ms ease-out, opacity 150ms 150ms ease-in;
			transform: translate3d(0, 0, 0);
		}
	</style>
	<link rel="stylesheet" media="all"
	      href="/css/ifttt.css"
	      data-turbolinks-track="true">
	<title></title>
</head>
<div>
	@yield('content')
	@include('javascript-in-body')
</div>
