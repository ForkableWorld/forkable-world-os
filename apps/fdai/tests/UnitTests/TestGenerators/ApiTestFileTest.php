<?php
namespace Tests\UnitTests\TestGenerators;
use Tests\TestGenerators\ApiTestFile;
use Tests\UnitTestCase;
/**
 * @package Tests\UnitTests\Files
 * @coversDefaultClass \Tests\TestGenerators\ApiTestFile;
 */
class ApiTestFileTest extends UnitTestCase {
	/**
	 * @covers ApiTestFile::generateNamePrefix
	 * @noinspection PhpUnitMissingTargetForTestInspection (from a trait)
	 */
	public function testGenerateNamePrefixApiTestFile(){
		$this->assertEquals("AstralApiConnectorRequests", ApiTestFile::generateNamePrefix("/astral-api/connector-requests/8"));
	}
}
