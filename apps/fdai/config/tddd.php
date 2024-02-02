<?php
return [
	'tddd-path' => realpath(__DIR__.'/../tests/Tddd'),
	'editors' => [
		'phpstorm' => [
			'code' => 'phpstorm',
			'name' => 'PHPStorm',
			'bin' => 'C:\Users\m\AppData\Local\JetBrains\Toolbox\apps\PhpStorm\ch-0\212.5284.49\bin\phpstorm64.exe {file}:{line}',
			'default' => true,
		],
		'sublime' => [
			'code' => 'sublime',
			'name' => 'SublimeText 3',
			'bin' => '/usr/local/bin/subl {file}:{line}',
		],
		'vscode' => [
			'code' => 'vscode',
			'name' => 'VSCode',
			'bin' => '/Applications/Visual\\ Studio\\ Code.app/Contents/Resources/app/bin/code --goto {file}:{line}',
		],
	],
	'notifications' => [
		'notify_on' => [
			'fail' => true,
			'pass' => false,
		],
		'routes' => [
			'dashboard' => 'tests-watcher.dashboard',
		],
		'action-title' => 'Tests Failed',
		'action_message' => 'One or more tests have failed.',
		'from' => [
			'name' => 'Test Driven Development Dashboard',
			'address' => 'tddd@mydomain.com',
			'icon_emoji' => '',
			'icon_url' => 'https://emojipedia-us.s3.amazonaws.com/thumbs/120/apple/96/lady-beetle_1f41e.png',
		],
		'users' => [
			'model' => 'PragmaRX\\TestsWatcher\\Package\\Data\\Models\\User',
			'emails' => [
				0 => 'tddd@mydomain.com',
			],
		],
		'channels' => [
			'mail' => [
				'enabled' => false,
				'sender' => 'PragmaRX\\TestsWatcher\\Package\\Notifications\\Channels\\Mail',
			],
			'slack' => [
				'enabled' => true,
				'sender' => 'PragmaRX\\TestsWatcher\\Package\\Notifications\\Channels\\Slack',
			],
		],
		'notifier' => 'PragmaRX\\TestsWatcher\\Notifications',
	],
	'pipers' => [
		'script-debian' => [
			//'bin' => '/usr/bin/script',
			//'execute' => '{$bin} -q -c \'{$command}\' {$tempFile}',
			'bin' => '',
			'execute' => '{$command}',
		],
		'script-macos' => [
			'bin' => '/usr/bin/script',
			'execute' => '{$bin} -q {$tempFile} {$command}',
		],
		'tee' => [
			'bin' => '/usr/bin/tee',
			'execute' => '{$command} | {$bin} > {$tempFile}',
		],
	],
	'projects' => [
		'phpunit' => [
			'name' => 'PHPUnit',
			'path' => '/www/wwwroot/qm-api',
			'watch_folders' => [
				0 => 'app',
			],
			'exclude' => [],
			'depends' => [],
			'tests_path' => 'tests',
			'suites' => [
				'Unit' => [
					'tester' => 'phpunit',
					'tests_path' => 'UnitTests',
					'command_options' => '',
					'file_mask' => '*Test.php',
					'retries' => 0,
				],
				'StagingUnit' => [
					'tester' => 'phpunit',
					'tests_path' => 'StagingUnitTests',
					'command_options' => '',
					'file_mask' => '*Test.php',
					'retries' => 0,
				],
				'DBUnit' => [
					'tester' => 'phpunit',
					'tests_path' => 'SlimTests',
					'command_options' => '',
					'file_mask' => '*Test.php',
					'retries' => 0,
				],
			],
		],
	],
	'projects.bak' => [
		'laravel-dusk' => [
			'name' => 'Laravel Dusk',
			'path' => '/www/wwwroot/qm-api/pragmarx/tddd',
			'watch_folders' => [
				0 => 'app',
				1 => 'resources',
				2 => 'config',
				3 => 'routes',
				4 => 'tests/Browser',
			],
			'exclude' => [
				0 => 'tests/Browser/console',
				1 => 'tests/Browser/screenshots',
			],
			'depends' => [],
			'tests_path' => 'tests',
			'suites' => [
				'browser' => [
					'tester' => 'dusk',
					'tests_path' => 'Browser',
					'command_options' => '',
					'file_mask' => '*Test.php',
					'retries' => 0,
				],
			],
		],
		'multiple-suites' => [
			'name' => 'Multiple suites',
			'path' => '/www/wwwroot/qm-api/pragmarx/tddd',
			'watch_folders' => [
				0 => 'app',
			],
			'exclude' => [
				0 => 'storage',
				1 => '.idea',
			],
			'depends' => [],
			'tests_path' => 'tests/Multiple/',
			'suites' => [
				'page_module' => [
					'tester' => 'phpunit',
					'tests_path' => 'Modules/Page/Tests',
					'command_options' => '',
					'file_mask' => '*Test.php',
					'retries' => 0,
				],
				'core_module' => [
					'tester' => 'phpunit',
					'tests_path' => 'Modules/Core/Tests',
					'command_options' => '',
					'file_mask' => '*Test.php',
					'retries' => 0,
				],
			],
		],
		'path-with-spaces' => [
			'name' => 'PHPUnit with spaces',
			'path' => '/www/wwwroot/qm-api/pragmarx/tddd/examples/phpunit with spaces',
			'watch_folders' => [
				0 => 'tests',
			],
			'exclude' => [],
			'depends' => [],
			'tests_path' => 'tests',
			'suites' => [
				'unit' => [
					'tester' => 'phpunit',
					'tests_path' => '',
					'command_options' => '',
					'file_mask' => '*Test.php',
					'retries' => 0,
				],
			],
		],
		'pragmarx-firewall' => [
			'name' => 'Firewall (PragmRX)',
			'path' => '/www/wwwroot/qm-api/pragmarx/tddd/vendor/pragmarx/firewall',
			'watch_folders' => [
				0 => 'src',
				1 => 'tests',
			],
			'exclude' => [
				0 => 'tests/database.sqlite',
				1 => 'tests/geoipdb',
				2 => 'tests/files',
			],
			'depends' => [],
			'tests_path' => 'tests',
			'suites' => [
				'unit' => [
					'tester' => 'phpunit',
					'tests_path' => '',
					'command_options' => '',
					'file_mask' => '*Test.php',
					'retries' => 0,
				],
			],
		],
		'react' => [
			'name' => 'React',
			'path' => '/www/wwwroot/qm-api/pragmarx/tddd/examples/react',
			'watch_folders' => [
				0 => 'src',
			],
			'exclude' => [],
			'depends' => [],
			'tests_path' => 'src',
			'suites' => [
				'unit' => [
					'tester' => 'react-scripts',
					'tests_path' => '',
					'command_options' => '',
					'file_mask' => '*.test.js',
					'retries' => 0,
				],
			],
		],
		'ruby-on-rails' => [
			'name' => 'Ruby on Rails',
			'path' => '/www/wwwroot/qm-api/pragmarx/tddd/examples/ruby-on-rails',
			'watch_folders' => [
				0 => 'app',
				1 => 'config',
				2 => 'db',
				3 => 'lib',
				4 => 'test',
			],
			'exclude' => [],
			'depends' => [],
			'tests_path' => 'test',
			'suites' => [
				'unit' => [
					'tester' => 'rake',
					'tests_path' => '',
					'command_options' => '',
					'file_mask' => '*_test.rb',
					'retries' => 0,
					'editor' => 'vscode',
				],
			],
		],
		'symfony-flex' => [
			'name' => 'Symfony (4.0-BETA) Flex',
			'path' => '/www/wwwroot/qm-api/pragmarx/tddd/examples/symfony4/vendor/symfony/flex',
			'watch_folders' => [
				0 => 'src',
				1 => 'tests',
			],
			'exclude' => [],
			'depends' => [],
			'tests_path' => 'tests',
			'suites' => [
				'unit' => [
					'tester' => 'simple-phpunit',
					'tests_path' => '',
					'command_options' => '',
					'file_mask' => '*Test.php',
					'retries' => 0,
				],
			],
		],
		'vanilla-javascript' => [
			'name' => 'Vanilla Javascript (Jest)',
			'path' => '/www/wwwroot/qm-api/pragmarx/tddd',
			'watch_folders' => [
				0 => 'examples/javascript',
			],
			'exclude' => [
				0 => 'storage',
				1 => '.idea',
			],
			'depends' => [],
			'tests_path' => 'examples/javascript/tests',
			'suites' => [
				'unit' => [
					'tester' => 'jest',
					'tests_path' => '',
					'command_options' => '',
					'file_mask' => '*.spec.js',
					'retries' => 0,
				],
			],
		],
		'vuejs-ava' => [
			'name' => 'VueJS (AVA)',
			'path' => '/www/wwwroot/qm-api/pragmarx/tddd/examples/vue-ava',
			'watch_folders' => [
				0 => 'src',
				1 => 'test',
			],
			'exclude' => [
				0 => 'node_modules',
			],
			'depends' => [],
			'tests_path' => 'test',
			'suites' => [
				'unit' => [
					'tester' => 'ava',
					'tests_path' => '',
					'command_options' => '',
					'file_mask' => '*.test.js',
					'retries' => 0,
					'editor' => 'vscode',
				],
			],
		],
		'vuejs-jest' => [
			'name' => 'VueJS (Jest)',
			'path' => '/www/wwwroot/qm-api/pragmarx/tddd/examples/vue-jest',
			'watch_folders' => [
				0 => 'src',
				1 => 'tests',
			],
			'exclude' => [
				0 => 'tests/__snapshots__',
			],
			'depends' => [],
			'tests_path' => 'tests',
			'suites' => [
				'unit' => [
					'tester' => 'jest',
					'tests_path' => '',
					'command_options' => '',
					'file_mask' => '*.test.js',
					'retries' => 0,
					'editor' => 'vscode',
				],
			],
		],
		'vuejs-test-utils' => [
			'name' => 'VueJS (vue-test-utils)',
			'path' => '/www/wwwroot/qm-api/pragmarx/tddd/examples/vue-test-utils',
			'watch_folders' => [
				0 => 'components',
			],
			'exclude' => [
				0 => 'node_modules',
			],
			'depends' => [],
			'tests_path' => 'components',
			'suites' => [
				'unit' => [
					'tester' => 'jest',
					'tests_path' => '',
					'command_options' => '',
					'file_mask' => '*.test.js',
					'retries' => 0,
					'editor' => 'vscode',
				],
			],
		],
	],
	'root' => [
		'names' => [
			'dashboard' => 'Test Driven Development Dashboard',
			'watcher' => 'TDDD - Watcher',
			'worker' => 'TDDD - Worker',
		],
		'regex_file_matcher' => '/([A-Za-z0-9\\/._-]+)(?::| on line )([1-9][0-9]*)/',
		'poll_interval' => 20000,
		'poll' => ['enable' => true],
		'tmp_dir' => '/var/tmp/',
		'show_progress' => true,
		'cache' => [
			'event_timeout' => 10,
		],
		'code' => [
			'path' => '/www/wwwroot/qm-api',
		],
		'coverage' => [
			'path' => 'coverage',
		],
		'broadcasting' => [
			'enabled' => true,
			'pusher' => [
				'driver' => 'pusher',
				'key' => env('PUSHER_APP_KEY'),
				'secret' => env('PUSHER_APP_SECRET'),
				'app_id' => env('PUSHER_APP_ID'),
				'options' => [
					'cluster' => env('PUSHER_APP_CLUSTER'),
					'encrypted' => true,
				],
				'channel_name' => 'tddd',
			],
		],
	],
	'routes' => [
		'prefixes' => [
			'global' => '',
			'dashboard' => '/tddd',
			'tests' => '/tddd/tests',
			'projects' => '/tddd/projects',
			'files' => '/tddd/files',
			'html' => '/tddd/html',
		],
	],
	'testers' => [
		'atoum' => [
			'code' => 'atoum',
			'name' => 'Atoum',
			'command' => 'sh vendor/bin/atoum',
			'pipers' => [
				0 => 'tee',
			],
		],
		'ava' => [
			'code' => 'ava',
			'name' => 'AVA',
			'command' => 'node_modules/.bin/ava --verbose',
			'error_pattern' => '[1-9]+\\s+(exception|failure)',
			'pipers' => [
				0 => 'script-macos',
			],
		],
		'behat' => [
			'code' => 'behat',
			'name' => 'Behat',
			'command' => 'sh vendor/bin/behat',
			'pipers' => [
				0 => 'script-macos',
			],
		],
		'codeception' => [
			'code' => 'codeception',
			'name' => 'Codeception',
			'command' => 'sh %project_path%/vendor/bin/codecept run',
			'output_folder' => 'tests/_output',
			'output_html_fail_extension' => '.fail.html',
			'output_png_fail_extension' => '.fail.png',
			'pipers' => [
				0 => 'script-macos',
			],
		],
		'dusk' => [
			'code' => 'dusk',
			'name' => 'Laravel Dusk',
			'command' => 'php artisan dusk',
			'output_folder' => '/Users/antoniocarlos/code/pragmarx/tddd/tests/Browser/screenshots',
			'output_html_fail_extension' => '.fail.html',
			'output_png_fail_extension' => '.fail.png',
			'error_pattern' => '(Failures|Errors): [0-9]+',
			'pipers' => [
				0 => 'script-macos',
			],
		],
		'jest' => [
			'code' => 'jest',
			'name' => 'Jest',
			'command' => 'npm test',
			'output_folder' => 'tests/__snapshots__',
			'output_html_fail_extension' => '.snap',
			'pipers' => [
				0 => 'script-macos',
			],
		],
		'phpspec' => [
			'code' => 'phpspec',
			'name' => 'phpspec',
			'command' => 'phpspec run',
			'pipers' => [
				0 => 'script-macos',
			],
		],
		'phpunit' => [
			'code' => 'phpunit',
			'name' => 'PHPUnit',
			'command' => 'vendor/phpunit/phpunit/phpunit --configuration phpunit.xml',
			'pipers' => [
				0 => 'script-macos',
			],
		],
		'rake' => [
			'code' => 'rake',
			'name' => 'Rake',
			'command' => 'bin/rails test',
			'error_pattern' => 'Test\\s+Suites:\\s+[0-9]+\\s+failed',
			'pipers' => [
				0 => 'script-macos',
			],
		],
		'react-scripts' => [
			'code' => 'react-scripts',
			'name' => 'React Scripts (Tester)',
			'env' => 'CI=true',
			'command' => 'npm test',
			'error_pattern' => 'Test\\s+Suites:\\s+[0-9]+\\s+failed',
			'pipers' => [
				0 => 'script-macos',
			],
		],
		'simple-phpunit' => [
			'code' => 'simple-phpunit',
			'name' => 'Simple PHPUnit (Symfony)',
			'command' => 'vendor/bin/simple-phpunit',
			'pipers' => [
				0 => 'script-macos',
			],
		],
		'tester' => [
			'code' => 'tester',
			'name' => 'Tester',
			'command' => 'sh vendor/bin/tester',
			'pipers' => [
				0 => 'script-macos',
			],
		],
	],
];
