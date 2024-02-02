<?php

require_once __DIR__ . '/bootstrap_script.php';
$command = $argv[0];
unset($argv[0]);
$params = [];
foreach ($argv as $key => $value) {
	if ($value != '') {
		$name = str_starts_with($key, 'argument') ? substr($key, 9) : '--' . substr($key, 7);
		$params[$name] = $value;
	}
}

artisan($command, $params);
