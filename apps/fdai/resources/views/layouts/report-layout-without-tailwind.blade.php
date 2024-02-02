<?php /** @var \App\Reports\AnalyticalReport $report */ ?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"https://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="https://www.w3.org/1999/xhtml" lang="en">
<!-- Start resources/views/layouts/report-layout.blade.php -->
<head>
	@include('meta')
	{{-- Messes up root cause reports	<link rel="stylesheet" href="{{ qm_asset('css/so-simple.css') }}">--}}
	<link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css">
	<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.dataTables.min.css">
	<link rel="stylesheet" href="https://adminlte.io/themes/AdminLTE/dist/css/skins/_all-skins.min.css">
	<link rel="stylesheet" href="https://adminlte.io/themes/AdminLTE/plugins/pace/pace.min.css">
	<style>
		.pace .pace-progress {
			background: #2299dd;
		}
	</style>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tarekraafat/autocomplete.js@7.2.0/dist/css/autoComplete.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.4.3/css/AdminLTE.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.4.3/css/skins/_all-skins.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.7.14/css/bootstrap-datetimepicker.min.css">
	@include('fontawesome')
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/skins/square/_all.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css">
	<link rel="stylesheet" href="https://code.getmdl.io/1.3.0/material.indigo-pink.min.css">  <!-- Need this for chips https://getmdl.io/components/index.html#chips-section -->
	<link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
	<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
	<link rel="stylesheet" href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://unpkg.com/material-components-web@v4.0.0/dist/material-components-web.min.css">

	<!--CUSTOM CSS BELOW HERE -->
	<link href="{{ qm_asset('css/material-card.css?v=2.1.1') }}" rel="stylesheet" />
	<link rel="stylesheet" href="https://static.quantimo.do/css/statistics-table.css">
	<link rel="stylesheet" href="https://static.quantimo.do/css/wp-button.css">
	<link rel="stylesheet" href="https://static.quantimo.do/css/medium-study.css">
	<link rel="stylesheet" href="https://static.quantimo.do/css/modern-AdminLTE.min.css">
	{{-- Messes up root cause reports	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss/dist/tailwind.min.css" >--}}
	@include('javascript-in-head')
</head>
<body class="body">
@include('gtm-body-tag')
<div
	class="page-wrapper"
	style="
        height: auto !important;
        max-width: {{\App\UI\CssHelper::GLOBAL_MAX_PAGE_WIDTH}}px;
        margin: auto;
        font-family: \'Source Sans Pro\', sans-serif;
        "
>
	{!! $content ?? $report->getShowContent() !!}
</div>
@include('javascript-in-body')
<!-- Don't put qm_api_asset('/js/app.js') in global javascript-in-body because it breaks material layout -->
{{-- Don't need app.js for reports <script src="{{ qm_api_asset('/js/app.js') }}" type="text/javascript"></script>--}}
@stack('scripts')
<!-- End resources/views/layouts/report-layout.blade.php -->
</body>
</html>
