<?php
namespace Tests\UnitTests\Properties\Base;
use App\Properties\Base\BaseClientIdProperty;
use Tests\UnitTestCase;
/**
 * @package Tests\UnitTests\Files
 * @coversDefaultClass \App\Properties\Base\BaseClientIdProperty;
 */
class BaseClientIdPropertyTest extends UnitTestCase {
	/**
	 * @covers BaseClientIdProperty::fromRequestJobOrSystem
	 */
	public function testFromRequestJobOrSystemBaseClientIdProperty(){
		BaseClientIdProperty::setInMemory(BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT);
		$this->assertEquals(BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT, BaseClientIdProperty::fromRequestJobOrSystem());
	}
}
