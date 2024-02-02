<?php
namespace Tests\UnitTests\Jobs;
use Tests\UnitTestCase;
/**
 * @package Tests\UnitTests\Files
 * @coversDefaultClass \App\Jobs\PHPUnitTestJob;
 */
class PHPUnitTestJobTest extends UnitTestCase {
	public function testQueuePHPUnitTestJob(){
		$this->skipTest("TODO");
		$this->queueTestLocally();
	}
}
