
@include('variable-category-chips')
<?php /** @var \App\Models\Variable[] $buttons */ ?>
{{-- Make sure to wrap items with an <a> tag so they're hidden --}}
<form onsubmit="return false;" style="margin-top: 1rem; margin-bottom: 1rem;">
	<div style="padding: 0.5em;
    width: 100%;
    font-size: 0.9em;
    border: 1px solid #cccccc;
    border-radius: 2rem;
    box-shadow: 0 1px 6px 0 rgba(32, 33, 36, 0.28);">
		<i class="fas fa-search" style="padding: 0.5rem"></i>
		<!--suppress HtmlFormInputWithoutLabel -->
		<input
			type="text"
			style="margin: 0; display: inline; width: 80%"
			id="variables-input"
			onkeyup="variableSearchFilter()"
			placeholder="Search for a variable..."
			autofocus>
	</div>
</form>

<!-- Start resources/views/ionic_js.blade.php -->
<script src="{{ qm_asset('lib/localforage/dist/localforage.js') }}"></script>
<script src="{{ qm_asset('lib/quantimodo/quantimodo-web.js') }}"></script>
<script src="{{ public_app_public_url('js/qmLogger.js') }}"></script>
<script src="https://static.quantimo.do/lib/q/q.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
<script src="{{ public_app_public_url('js/qmHelpers.js') }}"></script>
<script src="{{ public_app_public_url('data/appSettings.js') }}"></script>
<script defer src="{{ public_app_public_url('data/variableCategories.js') }}"></script>
<script defer src="{{ public_app_public_url('data/commonVariables.js') }}"></script>
<script defer src="{{ qm_url('js/variable-search-filter.js') }}"></script>
@include('sweetalert::alert')
<!-- End resources/views/search-filter-script.blade.php -->
@isset($heading)
	<h2 style="text-align: center;" class="text-3xl mb-2 font-semibold leading-normal">
		{{ $heading }}
	</h2>
@endisset
<?php /** @var \App\Buttons\QMButton[] $buttons */ ?>
<div id="variables-list" class="flex flex-wrap justify-center">
	@foreach( $buttons as $b )
		{!! $b->getChipMedium() !!}
	@endforeach
</div>

@include('not-found-box', ['table' => 'variables', 'searchId' => 'variables'])
