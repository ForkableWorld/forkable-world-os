<?php
use App\DataSources\Connectors\OuraConnector;
use App\Logging\QMLog;
use App\Models\Connection;
use App\PhpUnitJobs\Import\ConnectionsJob;
use App\Utils\AppMode;
require_once __DIR__.'/../php/bootstrap_script.php';
$process = basename(__FILE__, '.php');
AppMode::setJobOrTaskName($process);
QMLog::logStartOfProcess($process);
/** @var Connection $c */
$c = Connection::whereUserId(230)->where(Connection::FIELD_CONNECTOR_ID, OuraConnector::ID)->first();
$c->import(__FILE__);
$meta = $c->getUser();
$meta = $meta->generateNftMetadata();
QMLog::print($meta);
ConnectionsJob::import();
QMLog::logEndOfProcess($process);
