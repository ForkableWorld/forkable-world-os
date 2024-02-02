<!-- Start resources/views/loggers-js.blade.php -->
@if (config('app.env') !== 'production')
	<script src="https://cdn.jsdelivr.net/gh/underground-works/clockwork-browser@1/dist/toolbar.js"></script>
@endif
<script src="https://cdn.lr-ingest.io/LogRocket.min.js" crossorigin="anonymous"></script>
<script>if(window.location.href.indexOf("app.quantimo.do") > -1){LogRocket.init('mkcthl/quantimodo');}</script>
<script type="text/javascript" src="https://d2wy8f7a9ursnm.cloudfront.net/v7/bugsnag.min.js"></script>
<script type="text/javascript">Bugsnag.start('ae7bc49d1285848342342bb5c321a2cf')</script>
<script src="{{ qm_asset('js/qmLog.js') }}"></script>
<!-- End resources/views/loggers-js.blade.php -->
