<?php
namespace Tests\UnitTests;
use App\Logging\ConsoleLog;
use App\Repos\QMAPIRepo;
use App\Utils\EnvOverride;
use App\Utils\IonicHelper;
use Tests\UnitTestCase;
class GithubTest extends UnitTestCase
{
	protected function setUp(): void{
		$this->skipTest();
		parent::setUp();
	}
	/**
	 * @covers \App\Repos\QMAPIRepo::createStatus
	 */
	public function testSetCommitStatus(){
        if(!EnvOverride::isLocal()){
            $this->skipTest("Only test status for debug locally");
        } else {
            QMAPIRepo::getRateLimits();
            QMAPIRepo::createStatus(QMAPIRepo::getCommitShaHash(), QMAPIRepo::STATE_pending,
                IonicHelper::getChartsUrl(), "test short name", "test longer description");
            $statuses = QMAPIRepo::getStatuses();
            $this->assertCountGreaterThan(0, $statuses);
        }
    }
	/**
	 * @covers QMAPIRepo::getFailedStatuses
	 */
	public function testGetGithubStatuses(){
        if(strtotime("2020-12-21") > time()){
            $this->skipTest("Hopefully temporary");
            return;
        }
        ConsoleLog::info("Starting ".$this->getName());
        $statuses = QMAPIRepo::getStatuses();
        $this->assertCountGreaterThan(0, $statuses);
        if($checkFailed = false){
            $failed = QMAPIRepo::getFailedStatuses();
            ConsoleLog::info("Got ".count($failed)." failed statuses");
            $this->assertCount(0, $failed, \App\Logging\QMLog::print_r($failed, true));
            ConsoleLog::info("Finished ".$this->getName());
        }
    }
}
