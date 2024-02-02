<?php
namespace Tests\UnitTests\Slim\Model\User;
use App\Slim\Model\User\PublicUser;
use Tests\UnitTestCase;
/**
 * @package Tests\UnitTests\Files
 * @coversDefaultClass \App\Slim\Model\User\PublicUser;
 */
class PublicUserTest extends UnitTestCase {
	/**
	 * @covers PublicUser::getTitleAttribute
	 * @noinspection PhpUnitMissingTargetForTestInspection (from a trait)
	 */
	public function testGetDisplayNamePublicUser(){
		$obj = PublicUser::find(230);
		$this->assertEquals("Mike P. Sinn", $obj->getTitleAttribute());
	}
}
