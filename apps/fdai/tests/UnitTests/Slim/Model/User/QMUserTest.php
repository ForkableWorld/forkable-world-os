<?php
namespace Tests\UnitTests\Slim\Model\User;
use App\Models\User;
use App\Storage\DB\TestDB;
use App\Types\QMStr;
use Tests\UnitTestCase;
/**
 * @package Tests\UnitTests\Files
 * @coversDefaultClass \App\Slim\Model\User\QMUser;
 */
class QMUserTest extends UnitTestCase {
	/**
	 * @covers User::createNewUser
	 * @noinspection PhpUnitMissingTargetForTestInspection (from a trait)
	 */
	public function testCreateNewUserQMUser(){
        $user = User::whereUserLogin("client-id-oauth_test_client-user-id-100")->first();
        if($user){
            $user->hardDeleteWithRelations(__FUNCTION__);
        }
		$data = [
			'clientUserId' => 100,
			'clientId' => 'oauth_test_client',
			'email' => 'test-user@client.com',
			'user_login' => 'client-id-oauth_test_client-user-id-100',
			'client_id' => 'oauth_test_client',
			'reg_provider' => 'oauth_test_client',
			'provider_id' => 100,
			'unsubscribed' => true,
		];
		$l = User::createNewUser($data);
		$qmUser = $l->getQMUser();
		$this->assertEquals(100, $l->provider_id);
		$this->assertEquals(100, $qmUser->clientUserId);
		foreach($data as $key => $val){
			//if($key === 'clientUserId'){continue;}
			$snake = QMStr::snakize($key);
			$camel = QMStr::camelize($key);
			$this->assertEquals($val, $l->$snake, "l->$snake");
			if($snake === User::FIELD_USER_LOGIN){continue;}
			$this->assertEquals($val, $qmUser->$camel, "qmUser->$camel");
		}
	}
}
