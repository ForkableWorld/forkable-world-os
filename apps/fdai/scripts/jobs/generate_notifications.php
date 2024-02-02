<?php
use App\Logging\QMLog;
use App\Utils\AppMode;
require_once __DIR__.'/../php/bootstrap_script.php';
$process = basename(__FILE__, '.php');
AppMode::setJobOrTaskName($process);
QMLog::logStartOfProcess($process);
\App\PhpUnitJobs\Reminders\ReminderNotificationGeneratorJob::deleteOldAndCreateNewNotifications();
QMLog::logEndOfProcess($process);
