<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\SlimTests\Controllers;
use App\Models\Application;
use App\Models\OAClient;
use App\Properties\Application\ApplicationUserIdProperty;
use App\Properties\Base\BaseClientIdProperty;
use App\AppSettings\AppSettings;
use App\AppSettings\AppSettingsResponse;
use App\AppSettings\AppStatus\BuildStatus;
use App\Models\Collaborator;
use App\AppSettings\StaticAppData;
use App\DataSources\QMClient;
use App\Properties\Base\BaseClientSecretProperty;
use App\Properties\Base\BaseUrlProperty;
use App\Properties\Collaborator\CollaboratorTypeProperty;
use App\Properties\User\UserIdProperty;
use App\Storage\DB\TestDB;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use App\Variables\VariableSearchResult;
class AppSettingsTest extends \Tests\SlimTests\SlimTestCase {
    const CLIENT_ID = BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT;
	private $defaultApplicationOwnerUserId = ApplicationUserIdProperty::DEFAULT;
    public function testGetAppSettings(){
		$collaborators = Collaborator::all();
		foreach($collaborators as $collaborator){
			$app = Application::find($collaborator->app_id);
			$this->assertNotNull($app, "We should have an app with id $collaborator->app_id");
		}
		$studyApps = Application::whereStudy(true)->get();
		$this->assertCount(0, $studyApps);
		$apps = Application::all();
		foreach($apps as $app){
			$client = $app->getOAClient();
			$this->assertNotNull($client);
			$this->assertEquals($app->client_id, $client->client_id);
		}
        $body = $this->getAppSettingsWithClientSecret();
        $this->checkAppSettings($body->appSettings);
        $allAppSettings = $this->getAllAppSettings();
        $this->assertCount($apps->count(), $allAppSettings);
        $this->assertNotNull(QMClient::readonly()
            ->where(QMClient::FIELD_CLIENT_ID, 'mindfirst')->first(),
            "mindfirst should not have been deleted!");
    }
    /**
     * @param $privateConfig
     */
    private function checkPrivateConfig($privateConfig){
        $this->assertEquals(self::CLIENT_ID, $privateConfig->client_ids->Web);
        $this->assertEquals(BaseClientSecretProperty::TEST_CLIENT_SECRET, $privateConfig->client_secrets->Web);
    }
    /**
     * @param AppSettings|object $a
     */
    private function checkAppSettings($a){
        $this->assertEquals("OAuth Test Client", $a->appDisplayName);
        $this->assertNotNull($a->appDesign);
        $this->assertNotNull($a->additionalSettings);
        $this->assertEquals('com.quantimodo.' . self::CLIENT_ID, $a->additionalSettings->appIds->appIdentifier);
        $this->assertEquals("https://" . self::CLIENT_ID . BaseUrlProperty::WILDCARD_APEX_DOMAIN, $a->additionalSettings->downloadLinks->webApp);
        $this->assertEquals(self::CLIENT_ID, $a->clientId);
        $a->appStatus->buildStatus->chromeExtension = BuildStatus::STATUS_READY;
        $a->appStatus->betaDownloadLinks->chromeExtension = "https://s3.com";
    }
    public function testGetAppSettingsByAdminWithoutClientSecret(){
		$app = Application::where(Application::FIELD_CLIENT_ID, self::CLIENT_ID)->first();
		$owner = $app->getUser();
        $this->setAuthenticatedUser($owner->getId());
		$response = $this->slimGet('/api/v1/appSettings', [
			'clientId' => self::CLIENT_ID,
		    'includeClientSecret' => true
		]);
        $body = json_decode($response->getBody(), false);
        $this->assertEquals("oauth_test_secret", $body->appSettings->clientSecret);
	    $this->assertCount(1, $body->appSettings->users);
	    $this->assertCount(1, $body->appSettings->collaborators);
    }
    public function testGetAppSettingsWithoutClientSecretOrAdminCredentials(){
        $this->setAuthenticatedUser(null);
        $response = $this->slimGet('/api/v1/appSettings', ['clientId' => self::CLIENT_ID]);
        $body = json_decode($response->getBody(), false);
	    $appSettings = $body->appSettings;
	    $this->assertNotTrue(isset($appSettings->clientSecret));
	    $this->assertNotTrue(isset($appSettings->users));
	    $this->assertNotTrue(isset($appSettings->collaborators));
    }
    public function testGetCollaboratorsAndUsers(){
        $this->setAuthenticatedUser($this->defaultApplicationOwnerUserId);
        $appSettings = $this->getAppSettings(['designMode' => true]);
        $this->assertGreaterThan(0, count($appSettings->collaborators));
	    $this->assertGreaterThan(0, count($appSettings->users));
    }
    /**
     * @return void
     */
    public function testFreeUpgrade(){
		$this->skipTest("We don't have a free plan anymore");
        $this->setAuthenticatedUser(1);
        $response = $this->postApiV3('upgrade', ['clientId' => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT, 'userId' => 2]);
        $this->assertNotNull($response);
    }
    public function testPostAppSettingsWithDifferentClientIdQueryParam(){
	    $clientIdForQueryParam = BaseClientIdProperty::CLIENT_ID_QUANTIMODO;
		$app = Application::whereClientId($clientIdForQueryParam)->first();
		$owner = $app->getUser();
        $this->setAuthenticatedUser($owner->getId());
        $postResponse = $this->getAppSettingsWithClientSecret();
        $chromeUrl = "https://oauth_test_client.com";
        $postResponse->appSettings->appStatus->betaDownloadLinks->chromeExtension = $chromeUrl;
        $this->postAndGetDecodedBody('api/v1/appSettings', $postResponse, true, 201, ['clientId' => 
	        $clientIdForQueryParam]);
		$application = Application::findByClientId($postResponse->appSettings->clientId);
		$statusFromDB = $application->getAppStatus();
	    $this->assertEquals($chromeUrl, $statusFromDB->betaDownloadLinks->chromeExtension);
        $response = $this->slimGet('api/v1/appSettings',
	        ['clientId' => self::CLIENT_ID, 'clientSecret' => "oauth_test_secret"]);
	    $updatedBody = json_decode($response->getBody(), false);
	    /** @var AppSettings $gotten */
	    $gotten = $updatedBody->appSettings;
	    $this->assertEquals($postResponse->appSettings->clientId, $gotten->clientId);
        $this->assertEquals($chromeUrl, $gotten->appStatus->betaDownloadLinks->chromeExtension);
        $response = $this->slimGet('/api/v1/appSettings', ['clientId' => $clientIdForQueryParam]);
        $updatedBody = json_decode($response->getBody(), false);
        $this->assertNotEquals($chromeUrl, $updatedBody->appSettings->appStatus->betaDownloadLinks->chromeExtension);
    }
    /**
     * @return mixed
     */
    public function testGetAppSettingsWithClientSecretButNotLoggedIn(){
        $this->setAuthenticatedUser(null);
        $response = $this->slimGet('/api/v1/appSettings',
	        [
				'clientId' => self::CLIENT_ID,
		        'clientSecret' => "oauth_test_secret"
	        ]);
        $body = json_decode($response->getBody(), false);
	    /** @var AppSettings $appSettings */
	    $appSettings = $body->appSettings;
	    $this->assertCount(1, $appSettings->users, "Only logged in admins should be able to get users and collaborators");
        $this->assertCount(1, $appSettings->collaborators, "Only logged in admins should be able to get users and collaborators");
        // TODO: Decide if this should be included in all web requests of just during builds?
        //$this->assertNull($body->appSettings->privateConfig, "Only logged in admins should be able to get private config during builds");
        return $body;
    }
    /**
     * @return AppSettings[]
     */
    private function getAllAppSettings(): array{
	    $userId = ApplicationUserIdProperty::DEFAULT;
	    $this->setAuthenticatedUser($userId);
	    $collaborators = Collaborator::whereUserId($userId)->get();
	    $numberOfApps = 4;
	    $this->assertCount($numberOfApps, $collaborators);
	    $apps = Application::whereUserId($userId)->get();
	    $this->assertCount($numberOfApps, $apps);
		foreach($apps as $app){
			$client = OAClient::find($app->client_id);
			$this->assertNotNull($client, "We should have a client for $app->client_id");
			$owner = Collaborator::whereType(CollaboratorTypeProperty::TYPE_OWNER)
				->whereClientId($app->client_id)
				->first();
			$this->assertNotNull($client, "We should have an owner for $app->client_id");
		}
	    $allAppSettings = Application::getAllWhereUserIsCollaborator($userId);
		$this->assertCount($numberOfApps, $allAppSettings);
        $response = $this->slimGet('/api/v1/appSettings', ["all" => true]);
        /** @var AppSettings[] $allAppSettings */
        $allAppSettings = json_decode($response->getBody(), false)->allAppSettings;
        foreach ($allAppSettings as $appSetting){
            $this->assertNotNull(Collaborator::readonly()
                ->where(Collaborator::FIELD_CLIENT_ID, $appSetting->clientId)
                ->where(Collaborator::FIELD_USER_ID, $userId)
                ->first(), "We should not get apps for which we are not a collaborator");
        }
        return $allAppSettings;
    }
    public function testGetAllStaticAppDataAsOwner(){
        $this->setAuthenticatedUser($this->defaultApplicationOwnerUserId);
        /** @var StaticAppData $staticData */
        $response = $this->getApiV3('appSettings',
	        ["clientId" => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT, "includeStaticAppData" => true]);
        /** @var AppSettingsResponse $response */
        $staticData = $response->staticData;
        $this->assertNotNull($staticData->appSettings);
        $this->validateVariables($staticData);
        $this->assertNotNull($staticData->connectors);
        $this->assertNotNull($staticData->deepThoughts);
        $this->assertNotNull($staticData->dialogAgent);
        $this->assertNotNull($staticData->docs);
        $this->assertNotNull($staticData->units);
        $this->assertNotNull($staticData->variableCategories);
    }
    /**
     * @param StaticAppData $staticData
     */
    private function validateVariables($staticData){
        $this->assertNotNull($staticData->commonVariables);
        /** @var VariableSearchResult $mood */
        $mood = collect($staticData->commonVariables)->filter(function($v){
            return $v->variableId === OverallMoodCommonVariable::ID;
        })->first();
        $this->assertTrue($mood->outcome);
    }
	public function prepareTestDatabase(){
		$clientIdsToKeep = [
			'mindfirst',
			'oauth_test_client',
			'moodimodo',
			'quantimodo',
		];
		Application::whereNotIn(Application::FIELD_CLIENT_ID, $clientIdsToKeep)->forceDelete();
		$connectorClientIds = \App\Models\Connector::pluck('name')->all();
		$clientIdsToKeep = array_unique(array_merge($connectorClientIds, $clientIdsToKeep));
		sort($clientIdsToKeep);
		OAClient::whereNotIn(OAClient::FIELD_CLIENT_ID, $clientIdsToKeep)->forceDelete();
		Collaborator::query()->update([
			                              Collaborator::FIELD_USER_ID => UserIdProperty::USER_ID_MIKE,
		                              ]);
		$ids = Application::pluck(Application::FIELD_ID);
		Collaborator::whereNotIn(Collaborator::FIELD_APP_ID, $ids)->forceDelete();
		$applications = Application::all();
		/** @var Application $application */
		foreach($applications as $application){
			$application->setOwner(UserIdProperty::USER_ID_MIKE);
		}
		TestDB::copyStorageToFixtures();
	}
}
