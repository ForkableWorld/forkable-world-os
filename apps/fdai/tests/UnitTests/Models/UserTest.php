<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\UnitTests\Models;
use App\DataSources\QMClient;
use App\Files\FileHelper;
use App\Models\Application;
use App\Models\DeviceToken;
use App\Models\OAAccessToken;
use App\Models\OAClient;
use App\Models\User;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Base\BaseRolesProperty;
use App\Repos\QMAPIRepo;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\User\QMUser;
use App\Storage\CacheManager;
use App\Storage\DB\TestDB;
use Tests\UnitTestCase;
/**
 * Class UserTest
 * @package Testss
 * @coversDefaultClass \App\Models\User
 */
class UserTest extends UnitTestCase {
    public const loginRegisterRedirect = "https://oauth_test_client.quantimodo.com/#/app/onboarding?client_id=oauth_test_client&quantimodoAccessToken=";
    //use DatabaseTransactions;  This causes many nightmares
    public const PATH_TO_CHECK_AFTER_LOGOUT= '/account';
	public function testLifeForce(){
		QMAPIRepo::createComment("testLifeForce");
		FileHelper::copy('tests/fixtures/fitbit_db.sqlite', TestDB::STORAGE_QM_TEST_SQLITE);
		$u = User::find(1);
		$meta = $u->generateNftMetadataForScoreVariables();
		$expected = [
			'animation_url' => 'https://static.quantimo.do/img/robots/quantimodo-robot-puzzled-213-300.png',
			'attributes' => [
				0 => [
					'trait_type' => 'Daily Step Count',
					'max_value' => 7513,
					'display_type' => 'boost_number',
					'value' => 6639,
				],
				1 => [
					'trait_type' => 'Sleep Duration',
					'max_value' => 7.77,
					'display_type' => 'boost_number',
					'value' => 4.37,
				],
				2 => [
					'trait_type' => 'Resting Heart Rate (Pulse)',
					'max_value' => 96,
					'display_type' => 'boost_number',
					'value' => 93,
				],
				3 => [
					'trait_type' => 'Sleep Efficiency From Fitbit',
					'max_value' => 87,
					'display_type' => 'boost_percentage',
					'value' => 85,
				],
			],
			'description' => 'Health biomarker values comprising the Life Force for PHPUnit Test User',
			'external_url' => 'https://testing.quantimo.do/datalab/users/1',
			'image' => 'https://static.quantimo.do/img/robots/quantimodo-robot-puzzled-213-300.png',
			'name' => 'PHPUnit Test User Life Force',
		];
		$this->assertArrayEquals($expected, $meta);
		$this->setAuthenticatedUser(1);
		$gotten = $this->getApiV6('lifeForce');
		$this->assertArrayEquals($expected, $gotten);
	}
	public function testGetQMUser(){
		$u = User::find(1);
		$qmUser = $u->getQMUser();
		$this->assertEquals($qmUser->firstName, $u->first_name);
		$this->assertEquals($qmUser->lastName, $u->last_name);
	}
	/**
	 * @covers RegisterController::postRegister
	 */
	public function testPostRegisterAndLogin(){
        CacheManager::flushTestCache();
        $u = QMUser::findByEmail("testuser1499206388501@gmail.com");
        if($u){$u->hardDeleteWithRelations("testing");}
        $postData = '{
          "_token": "test-token",
          "user_login": "testuser1499206388501",
          "user_email": "testuser1499206388501@gmail.com",
          "user_pass": "qwerty",
          "user_pass_confirmation": "qwerty",
          "terms": "1"
        }';
		$expectedRedirectPath = 'https://testing.quantimo.do/app/public/#/app/onboarding';
		$response = $this->assertPostRedirect('/auth/register', $postData, $expectedRedirectPath);
        $redirect = $response->headers->get('Location');
        $this->assertNotContains('&amp;', $redirect);
        $this->assertStringStartsWith($expectedRedirectPath, $redirect);
        QMAuth::logout(__METHOD__);
        $postData = '{
          "_token": "test-token",
          "user_login": "testuser1499206388501",
          "user_email": "testuser1499206388501@gmail.com",
          "user_pass": "qwerty",
          "terms": "1"
        }';
        $this->assertPostRedirect('/auth/login', $postData,
                                  $expectedRedirectPath);  // 'account' wast the page we tested after logout in $this->logout();
    }
	/**
	 * @return void
	 * @covers \App\Http\Controllers\Auth\LoginController::redirectWithAccessToken
	 */
	public function testAuthPages(){
        $this->getResponseContains('auth/register',"Sign Up");
        $this->getResponseContains('auth/login',"Sign In");
        $this->actingAsUserOne();
//        $this->assertGetRedirect('auth/login', 'https://web.quantimo.do/#/app/onboarding?quantimodoAccessToken=', 'We Should Be Redirected To Onboarding Since We Are Already Logged In');
//        $this->assertGetRedirect('auth/register', 'https://web.quantimo.do/#/app/onboarding?quantimodoAccessToken=');
        // Redirecting from login page when logged in causes infinite redirect sometimes
        //$this->getResponseContains('auth/register',"Sign Up");
        //$this->getResponseContains('auth/login',"Sign In");
        $this->getResponseContains('account',"Account");
    }
    public function testShareData(){
        $this->actingAsUserOne();
		$physician = User::physician();
        $clientId = $physician->getPhysicianClientId();
        $email = 'dr@quantimo.do';
        $this->postWithClientId('physician/share',
            ['physician_email' => $email, 'clientId' => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT]);
        $this->assertEquals(201, $this->getTestResponse()->getStatusCode(), $this->getTestResponse()->getContent());
        $this->assertNotNull(User::where('user_email',$email)->first());
        $this->assertNotNull(Application::where(QMClient::FIELD_CLIENT_ID, $clientId )->first());
        $this->assertNotNull(OAClient::where(QMClient::FIELD_CLIENT_ID, $clientId )->first());
        $this->assertNotNull(OAAccessToken::where(QMClient::FIELD_CLIENT_ID, $clientId )->first(),
            "Could not get dr@quantimo.do client!");
        $physician = User::findByEMail($email);
        $this->assertEquals(1, $physician->number_of_patients);
        $this->assertNotNull(OAAccessToken::where(QMClient::FIELD_USER_ID,1)->first());
    }
	public function testGetUrl(){
		$this->assertEquals(\App\Utils\Env::getAppUrl()."/datalab/users/18535", User::testUser()->getUrl());
	}
	/**
	 * @covers \App\Models\User::device_tokens
	 */
	public function testDeviceTokens(){
		$t = DeviceToken::firstOrFakeSave();
		$u = $t->getUser();
		$qb = $u->device_tokens();
		$tokens = $qb->get();
		$this->assertGreaterThan(0, $tokens->count());
	}
	/**
	 * @covers User::getRolesAttribute
	 */
	public function testGetRolesAttribute(){
		$admin = User::mike();
		$roles = $admin->getRolesAttribute();
		$this->assertIsArray($roles);
		$this->assertContains(BaseRolesProperty::ROLE_ADMINISTRATOR, $roles);
		$prop = $admin->getPropertyModel(BaseRolesProperty::NAME);
		$this->assertIsString($prop->getDBValue());
		$this->assertIsArray(json_decode($prop->getDBValue()));
		$this->assertIsArray($prop->getAccessorValue());
		$this->assertTrue($admin->isAdmin());
	}
	public function testCreateByEthAddress(){
		$u = User::createByEthAddress("0x0000000000000000000000000000000000000000");
		$this->assertEquals("0x0000000000000000000000000000000000000000", $u->eth_address);
	}

}
