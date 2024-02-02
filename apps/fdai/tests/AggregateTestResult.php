<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests;
use App\Slim\Model\DBModel;
class AggregateTestResult extends DBModel {
    public $ct;
    public $wt;
    public $cpu;
    public $mu;
    public $pmu;
    public $writableQueryCount;
    public $readOnlyQueryCount;
    public $totalResponseSize;
    public $clientId;
    public $timestamp;
    public $profileId;
    public $profileUrl;
}
