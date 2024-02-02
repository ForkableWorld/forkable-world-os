<?php
use Tests\QMDebugBar;
if (!defined('PROJECT_ROOT')) {define('PROJECT_ROOT', dirname(__DIR__, 2));}
require_once PROJECT_ROOT.'/vendor/autoload.php';
// Import is done during bootstrap which is run each time runTestsInFolder is called \App\Storage\DB\TestDB::importAndMigrateTestDB(false);
QMDebugBar::updateCollectorData();