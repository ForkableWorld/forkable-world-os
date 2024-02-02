<?php

return [
    'enabled' => env('APP_DEBUG') === true,
    'showBar' => env('APP_ENV') !== 'production',
    'showException' => true,
    'route' => [
        'prefix' => 'api/v2/tracy',
        'as' => 'tracy.',
    ],
    'accepts' => [
        'text/html',
    ],
    'appendTo' => 'body',
    'editor' => 'phpstorm://open?url=file://%file&line=%line',
    'maxDepth' => 4,
    'maxLength' => 1000,
    'scream' => true,
    'showLocation' => true,
    'strictMode' => true,
    'editorMapping' => [
		'/home/vagrant/qm-api/' => 
			'\\wsl$\Ubuntu-22..04\www\wwwroot\qm-api\\'
    ],
    'panels' => [
        'routing' => true,
        'database' => true,
        'view' => true,
        'event' => false,
        'session' => true,
        'request' => true,
        'auth' => true,
        'html-validator' => false,
        'terminal' => true,
    ],
];
