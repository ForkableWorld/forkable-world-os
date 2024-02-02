<!doctype html>
<html class="theme-<?=$config['theme']?>">
<!--
<?=$throwableString?>
-->
<head>
    <!-- Hide dumps asap -->
    <style>
        pre.sf-dump {
            display: none !important;
        }
    </style>

    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="robots" content="noindex, nofollow">

    <title><?= $title ?></title>

    <?php foreach ($styles as $script): ?>
        <link rel="stylesheet" href="<?=$housekeepingEndpoint?>/styles/<?=$script?>">
    <?php endforeach; ?>

</head>
<body class="scrollbar-lg">

<script>
    window.data = <?=
        $jsonEncode([
            'report' => $report,
            'config' => $config,
            'solutions' => $solutions,
            'telescopeUrl' => $telescopeUrl,
            'shareEndpoint' => $shareEndpoint,
            'defaultTab' => $defaultTab,
            'defaultTabProps' => $defaultTabProps,
        ])
    ?>

    window.tabs = <?=$tabs?>;
</script>

<noscript><pre><?=$throwableString?></pre></noscript>

<div id="app"></div>

<script><?= $getAssetContents('ignition.js') ?></script>
<script>
    window.Ignition = window.ignite(window.data);
</script>
<?php foreach ($scripts as $script): ?>
    <script src="<?=$housekeepingEndpoint?>/scripts/<?=$script?>"></script>
<?php endforeach; ?>
<script>
    Ignition.start();
</script>
<script
	src="https://code.jquery.com/jquery-3.4.1.min.js"
	integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
	crossorigin="anonymous"></script>
<script>

	$(document).ready(function() {
		return;
		// Get all phpstorm:// protocol links
		var $links = $('a[href^=phpstorm]');

		$links.each(function(index) {
			var $link = $(this);
			var href = $link.attr('href');

			// Drop the protocol, which doesnt work on linux or windows
			href = href.replace('phpstorm://open?', 'http://localhost:63342/api/file?');

			// Add the project path so PHPstorm knows which window to make active
			//href = href + '&project={{ $projectPath }}';
			$link.attr('href', href);

			// Send as an XHR request, so we don't redirect the user or have to open a new window
			$link.on('click', function(e) {
				e.preventDefault();

				$.get(href);
			})
		})
	});
</script>
<!--
<?=$throwableString?>
-->
</body>
</html>
