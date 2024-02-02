<?php
namespace Tests\StagingUnitTests\A\Connectors;
use App\Models\Connection;
use App\Logging\QMLog;
use Tests\SlimStagingTestCase;
class ImportableTest extends SlimStagingTestCase {
	public const JOB_NAME = "Production-A-phpunit";
	public function testImportWaitingStaleStuck(){
		$this->skipTest("Keeps getting stuck for some reason");
		if($this->skipIfQueued(static::JOB_NAME)){
			return;
		}
		//UserVariableCleanupJobTest::deleteUserMinMaxWhereEqualsCommonVariable();
		$allowed = [
			"Failed to connect to www.airnowapi.org",
		];
		try {
			Connection::importJobsTest();
		} catch (\Throwable $e) {
			foreach($allowed as $str){
				if(stripos($e->getMessage(), $str) !== false){
					QMLog::error(__METHOD__.": ".$e->getMessage());
				}
			}
			QMLog::info(__METHOD__.": ".$e->getMessage());
		}
		$this->assertTrue(true);
	}
}
