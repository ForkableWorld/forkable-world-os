<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace Tests\StagingUnitTests;
use Tests\SlimStagingTestCase;
use App\Models\User;
class UserTest extends SlimStagingTestCase
{
    public function testUser(): void{
		$l = User::find(91865);
		$l->test();
		$this->checkTestDuration(10);
		$this->checkQueryCount(5);
	}
	public function testInstantiateQMUser(){
		$l = User::find(3292);
		$qmUser = $l->instantiateQMUser();
		$this->assertEquals($qmUser->loginName, $l->user_login);
	}
}
