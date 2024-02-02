<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\SlimTests\Controllers;
use App\Exceptions\BadRequestException;
use App\Logging\QMLog;
use App\Models\User;
use App\Models\UserVariable;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Base\BasePasswordProperty;
use App\Properties\Base\BaseUserLoginProperty;
use App\Properties\User\UserIdProperty;
use App\Slim\Controller\User\DeleteUserController;
use App\Storage\DB\QMQB;
use App\Storage\DB\TestDB;
use App\Utils\QMCookie;
use Tests\QMBaseTestCase;
use Tests\SlimTests\SlimTestCase;
/**
 * Class UserTest
 * @package Tests\Api\Controllers
 */
class UserTest extends \Tests\SlimTests\SlimTestCase {
	public function testGetAuthenticatedUser(){
        TestDB::resetUserTables();
        $userId = 1;
        $this->setAuthenticatedUser($userId);
        $apiUrl = '/api/user/me';
        $parameters = [];
        $response = $this->slimGet($apiUrl, $parameters);
        $user = json_decode($response->getBody(), false);
        $this->assertEquals(BaseUserLoginProperty::TEST_USERNAME_QUANTIMODO, $user->loginName);
        $this->assertEquals(false, $user->administrator);
        $this->assertEquals($userId, $user->id);
        $this->assertQueryCountLessThan(10);
    }
	/**
	 * @return void
	 * @covers \Illuminate\Auth\SessionGuard::getName()
	 */
	public function testLoginAndPasswordAuthentication(){
        $userId = UserIdProperty::USER_ID_TEST_USER;
        $parameters = ['log' => BaseUserLoginProperty::TEST_USERNAME_18535, 'pwd' => BasePasswordProperty::TEST_USER_PASSWORD_18535,];
        $response = $this->slimGet('/api/user/me', $parameters);
        $user = json_decode($response->getBody(), false);
        $this->assertEquals(BaseUserLoginProperty::TEST_USERNAME_18535, $user->loginName);
        $this->assertFalse($user->administrator);
        $this->assertEquals($userId, $user->id);
        //$cookieName = "remember_web_59ba36addc2b2f9401580f014c7f58ea4e30989d";
	    //$authCookie = $response->cookies->get($cookieName);
		$cookies = $response->cookies->all();
		// From \Illuminate\Auth\SessionGuard::getName()
	    // Changed by using Impersonate Guard
		$login_cookie_name = "login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d";
		//$login_cookie_name = "login_web_3dc7a913ef5fd4b890ecabe3487085573e16cf82";
		if(!isset($cookies[$login_cookie_name])){
			$this->fail("Login cookie not set on ".QMLog::print_r($cookies));
		}
	    $authCookie = $cookies[$login_cookie_name];
	    unset($cookies[$login_cookie_name]["expires"]);
	    unset($cookies[$login_cookie_name]["value"]);
		$this->assertArrayEquals(array (
			                         $login_cookie_name =>
				                         array (
					                         'domain' => '.quantimo.do',
					                         'path' => '/',
					                         'secure' => true,
					                         'httponly' => false,
				                         ),
		                         ), $cookies);
	    $this->assertValidLoginCookie($authCookie, $userId);
    }
    public function testGetUserStats(){
		UserVariable::query()->update([UserVariable::FIELD_UPDATED_AT => now_at()]);
        $responseBody = $this->getAndDecodeBody('api/v3/apiStats', []);
        $this->assertEquals(2, $responseBody->apiStats->monthlyActiveUsers);
        $qb = User::query();
        QMQB::notLike($qb, 'user_email', "%test%");
        $totalUsers = $qb
            ->count();
        $this->assertEquals($totalUsers, $responseBody->apiStats->totalUsers);
        $this->assertNotNull($responseBody->apiStats->totalAggregatedStudies);
    }
	public function testCreateUser(){
		$time = time();
		$username = 'testUser' . $time . "@gmail.com";
		$parameters = [
			'log' => $username,
			'pwd' => $time,
			'pwdConfirm' => $time,
			'register' => true,
			'clientId' => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT,
		];
		$response = $this->slimPost('/api/v3/userSettings', $parameters);
		/** @var \App\Slim\Model\User\QMUser $user */
		$body = json_decode($response->getBody(), false);
		$user = $body->data->user;
		$this->assertEquals($username, $user->loginName);
		$this->assertEquals($username, $user->email);
	}
    public function testTryToCreateNewUserTwice(){
        $this->setAuthenticatedUser(null);
        $clientId = BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT;
        $clientUserId = 74802;
        $response = $this->slimGet('/api/v3/user', ['clientId' => $clientId, 'clientUserId' => $clientUserId]);
        $jsonBody = $response->getBody();
        $user = json_decode($jsonBody);
        $this->assertEquals($clientId, $user->clientId);
        $this->assertEquals($clientUserId, $user->clientUserId);
        QMBaseTestCase::setExpectedRequestException(BadRequestException::class);
        // TODO $this->slimGet('/api/v3/user', ['clientId' => $clientId, 'clientUserId' => $clientUserId], 400);
    }
    public function testDeleteUser(){
        TestDB::resetUserTables();
        $this->assertUserLoginNotEmpty();
        $user = $this->getOrSetAuthenticatedUser(1);
        $this->assertFalse($user->isTestUser());
        $user->updateQMClientId(BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT);
        $expectedReason = 'I hate you!';
        $this->setAuthenticatedUser(1);
        $this->assertUserLoginNotEmpty();
        $response = $this->slimDelete(DeleteUserController::ENDPOINT,
            ['clientId' => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT, 'reason' => $expectedReason]);
        $this->assertUserLoginNotEmpty();
        $jsonBody = $response->getBody();
        $this->assertUserLoginNotEmpty();
        $body = json_decode($jsonBody);
        $this->assertEquals(204, $body->status);
        $userRow = User::findDeleted(1);
        $this->assertContains('deleted', $userRow->user_email);
        $this->assertEquals($expectedReason, $userRow->deletion_reason);
        $this->assertUserLoginNotEmpty();
        $user = User::find(1);
        $this->assertUserLoginNotEmpty();
        $this->assertNull($user);
        User::unDeleteUser(1);
        $this->assertUserLoginNotEmpty();
        $user = User::find(1);
        $this->assertUserLoginNotEmpty();
        $this->assertNotNull($user);
        $this->setAuthenticatedUser(null);
    }
    private function getUserLogin(): string {
        $row = User::withTrashed()->find(1);
        return $row->user_login;
    }
    private function assertUserLoginNotEmpty(){
        $ul = $this->getUserLogin();
        $this->assertNotEmpty($ul);
    }
	/**
	 * @param array $authCookie
	 * @param int $userId
	 */
	private function assertValidLoginCookie(array $authCookie, int $userId): void{
		$u = User::find($userId);
		$this->assertEquals(QMCookie::DEFAULT_PATH, $authCookie['path']);
		$this->assertEquals(QMCookie::DEFAULT_DOMAIN, $authCookie['domain']);
		$this->assertEquals(QMCookie::DEFAULT_SECURE, $authCookie['secure']);
		//$this->assertEquals(QMCookie::DEFAULT_HTTP_ONLY, $authCookie['http_only']);
		$expiresRoughly = time() + QMCookie::COOKIE_SESSION_LIFETIME_IN_SECONDS;
		$t = QMCookie::generateExpirationTime();
		$expiresActual = $authCookie['expires'];
		$this->assertDateGreaterThan($expiresRoughly - 100, $expiresActual);
		$this->assertDateLessThan($expiresRoughly + 100, $expiresActual);
		$value = QMCookie::decrypt($u->getRememberTokenName(), $authCookie['value']);
		$this->assertEquals($userId, $value);
	}
}
