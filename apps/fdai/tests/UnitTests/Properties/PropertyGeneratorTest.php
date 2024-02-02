<?php
namespace Properties;
use Tests\UnitTestCase;
use Tests\UnitTests\Host;

class PropertyGeneratorTest extends UnitTestCase
{
	protected function setUp(): void{
		$this->skipIfNotLocal("Can't reproduce locally");
		parent::setUp();
	}
    public function testGenerateConnectionProperties(){
		$this->markTestIncomplete();
        $gen = Host::generateProperties();
        $this->assertNotNull($gen);
    }
}
