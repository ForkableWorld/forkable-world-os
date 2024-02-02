<?php
use App\Computers\ThisComputer;
use App\Repos\QMAPIRepo;
require_once __DIR__ . '/bootstrap_script.php';
QMAPIRepo::setSuitePending('cypress');
Artisan::call('serve', ['--host' => '127.0.0.1', '--port' => 5001]);
ThisComputer::exec('npm run cy:serve:run');
