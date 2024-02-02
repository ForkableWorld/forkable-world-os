<?php

return [
	// Path to output directory (default is build/allure-results)
	'outputDirectory' => 'build/allure-results',
	'linkTemplates' => [
		// Class or object must implement \Qameta\Allure\Setup\LinkTemplateInterface
		//'tms' => \My\LinkTemplate::class,
	],
	'setupHook' => function (): void {
		// Some actions performed before starting the lifecycle
	},
	// Class or object must implement \Qameta\Allure\PHPUnit\Setup\ThreadDetectorInterface
	//'threadDetector' => \My\ThreadDetector::class,
	'lifecycleHooks' => [
		// Class or object must implement one of \Qameta\Allure\Hook\LifecycleHookInterface descendants.
		//\My\LifecycleHook::class,
	],
];
