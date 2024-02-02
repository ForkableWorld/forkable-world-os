<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\A\Connectors;
use Tests\SlimStagingTestCase;
use App\Models\Connection;
class ConnectionUpdateQuantiModoTest extends SlimStagingTestCase {
	public function testConnectionUpdateQuantiModo(){
		$c = Connection::getConnectionById(1, 72);
		try {
			$c->import(__METHOD__);
			$this->fail("Should have thrown LogicException");
		} catch (\LogicException $e) {
			$this->assertInstanceOf(\LogicException::class, $e);
		}
		$this->checkTestDuration(12);
		$this->checkQueryCount(4);
	}
}
