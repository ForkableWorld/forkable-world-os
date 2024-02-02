<!-- Start resources/views/ionic_js.blade.php -->
<script src="{{ qm_asset('lib/localforage/dist/localforage.js') }}"></script>
<script src="{{ qm_asset('lib/quantimodo/quantimodo-web.js') }}"></script>
<script src="{{ public_app_public_url('js/qmLogger.js') }}"></script>
<script src="https://static.quantimo.do/lib/q/q.js"></script>
<script src="{{ public_app_public_url('js/qmHelpers.js') }}"></script>
<script src="{{ public_app_public_url('data/appSettings.js') }}"></script>
<script defer src="{{ public_app_public_url('data/qmStates.js') }}"></script>
<script defer src="{{ public_app_public_url('data/stateNames.js') }}"></script>
<script defer src="{{ public_app_public_url('data/units.js') }}"></script>
<script defer src="{{ public_app_public_url('data/variableCategories.js') }}"></script>
<script defer src="{{ public_app_public_url('data/commonVariables.js') }}"></script>
<script defer src="{{ public_app_public_url('data/docs.js') }}"></script>
<script defer src="{{ public_app_public_url('data/dialogAgent.js') }}"></script>
<script defer src="{{ qm_url('js/search-filter.js') }}"></script>
@include('sweetalert::alert')
<!-- End resources/views/ionic_js.blade.php -->
