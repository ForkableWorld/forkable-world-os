<?php
namespace Tests\UnitTests\Types;
use App\Types\QMArr;
use Tests\UnitTestCase;
/**
 * @package Tests\UnitTests\Files
 * @coversDefaultClass \App\Types\QMArr
 */
class QMArrTest extends UnitTestCase {
	/**
	 * @covers QMArr::toArray
	 * @noinspection PhpUnitMissingTargetForTestInspection (from a trait)
	 */
	public function testToArrayQMArr(){
		$this->assertEquals([], QMArr::toArray("[

]
"));
	}
}
