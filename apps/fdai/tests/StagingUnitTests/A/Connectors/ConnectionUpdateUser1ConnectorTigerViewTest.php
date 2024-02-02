<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace Tests\StagingUnitTests\A\Connectors;
use App\Models\Connection;
use Tests\SlimStagingTestCase;
class ConnectionUpdateUser1ConnectorTigerViewTest extends SlimStagingTestCase {
	public function testConnectionUpdateUser1ConnectorTigerView(): void{
		if(true){
			$this->skipTest('This test is too slow');
			return;
		}
		Connection::getConnectionById(1, 89)->import(__METHOD__);
		$this->checkTestDuration(66);
		$this->checkQueryCount(2);
	}
}
