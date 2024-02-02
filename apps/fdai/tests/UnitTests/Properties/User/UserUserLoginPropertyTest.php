<?php
namespace Tests\UnitTests\Properties\User;
use App\Properties\User\UserUserLoginProperty;
use Tests\UnitTestCase;
/**
 * @package Tests\UnitTests\Files
 * @coversDefaultClass \App\Properties\User\UserUserLoginProperty;
 */
class UserUserLoginPropertyTest extends UnitTestCase {
	/**
	 * @covers \App\Properties\User\UserUserLoginProperty::pluckOrDefault
	 * @covers \App\Properties\User\UserUserLoginProperty::pluck
	 */
	public function testUserLoginPropertyPluckOrDefault(){
		$arr = ['email' => 'staging-test-user@quantimo.do', 'password' => 'password',];
		$name = UserUserLoginProperty::pluck($arr);
		$this->assertNull($name);
		$name = UserUserLoginProperty::pluckOrDefault($arr);
		$this->assertEquals("staging-test-user-quantimo-do", $name);
	}
}