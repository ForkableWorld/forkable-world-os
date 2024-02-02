<?php
namespace Tests\UnitTests\Traits;
use Tests\UnitTestCase;
/**
 * @package Tests\UnitTests\Files
 * @coversDefaultClass \App\Traits\HasMemory
 */
class HasMemoryTest extends UnitTestCase {
	public function testGetUUID(){
		$userId = 1;
		$uv = $this->getMoodUserVariable($userId);
		$this->assertEquals($uv->getUUID(), "$userId-".$uv->getVariableIdAttribute());
	}
}
