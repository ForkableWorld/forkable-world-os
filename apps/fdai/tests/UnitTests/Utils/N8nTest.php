<?php
namespace Tests\UnitTests\Utils;
use App\Utils\EnvOverride;
use App\Utils\N8N;
use Tests\UnitTestCase;
/**
 * @package Tests\UnitTests\Utils
 * @coversDefaultClass N8N
 */
class N8nTest extends UnitTestCase {
	protected function setUp(): void{
		$this->skipTest();
		parent::setUp();
	}
	/**
	 * @covers \App\Utils\N8N::openUrl
	 * @noinspection PhpUnitMissingTargetForTestInspection (from a trait)
	 */
	public function testN8nWebHookPHPStormUrl(){
		$this->skipIfNotLocal("Don't want it randomly opening from tests");
		$body = N8N::openUrl($this->getPHPStormUrl());
		$this->assertEquals("Workflow got started.", $body['message']);
	}
}
