<?php
use App\Logging\QMLog;
use App\Utils\AppMode;
use Tests\QMBaseTestCase;
require_once __DIR__.'/../php/bootstrap_script.php';
$process = basename(__FILE__, '.php');
AppMode::setJobOrTaskName($process);
QMLog::logStartOfProcess($process);
QMBaseTestCase::queueTestsInFolder('tests/UnitTests');
	//, 'https://local.quantimo.do/admin/phpunit');

QMLog::logEndOfProcess($process);
