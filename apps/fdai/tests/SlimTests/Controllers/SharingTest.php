<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\SlimTests\Controllers;
use App\AppSettings\BaseApplication;
use App\Logging\QMLog;
use App\Models\Collaborator;
use App\Exceptions\InsufficientScopeException;
use App\Mail\QMSendgrid;
use App\Models\Application;
use App\Models\OAAccessToken;
use App\Models\OAClient;
use App\Models\User;
use App\Slim\Configuration\RouteConfiguration;
use App\Slim\Controller\Share\ShareResponse;
use App\Slim\Model\User\QMUser;
use Tests\QMBaseTestCase;
class SharingTest extends \Tests\SlimTests\SlimTestCase {
    private $physicianEmail = 'new-dr@quantimo.do';
    public function testReadOnlyPhysicianSharing(){
	    //TestDB::resetTestDB();
		OAAccessToken::truncate();
	    $response = $this->assertAuthorizedClients(0, 0);
        User::whereUserEmail($this->physicianEmail)->forceDelete();
        $this->setAuthenticatedUser(1);
        $patient = $this->getOrSetAuthenticatedUser(1);
	    $response = $this->assertAuthorizedClients(0, 0);
        $this->inviteToAccessDataAndCheckDBRecords();
	    $response = $this->assertAuthorizedClients(1, 0);
        $this->verifyReadOnlyAccess();
        $this->checkUnShareAuthorizedClientsResponse();
    }
    public function checkUnShareAuthorizedClientsResponse(): void{
	    $response = $this->assertAuthorizedClients(1, 0);
		$individuals = $response->authorizedClients->individuals;
	    /** @var BaseApplication $client */
        $client = $individuals[0];
        $scope = $client->scope;
        $this->assertEquals(RouteConfiguration::SCOPE_READ_MEASUREMENTS, $scope);
        $response = $this->deleteShare($client->clientId, 1);
        $this->assertCount(0, $response->authorizedClients->studies);
        $this->assertCount(0, $response->authorizedClients->individuals);
		$this->logout();
		$this->setAuthenticatedUser(1);
        $afterUnshare = $this->getAndDecodeBody('v1/shares');
        $this->assertAuthorizedClients(0, 0);
    }
    public function inviteToAccessDataAndCheckDBRecords(): void{
        $usersBefore = User::pluck(User::FIELD_USER_EMAIL);
        $this->assertNotContains($this->physicianEmail, $usersBefore->all());
        $clientsBefore = OAClient::count();
        $collaboratorsBefore = Collaborator::count();
        $appsBefore = Application::count();
        $postData = [
            'physician_email' => $this->physicianEmail,
            'scopes'          => 'readmeasurements'
        ];
        $response = $this->postApiV3('shares', $postData);
        $lastEmail = QMSendgrid::getLastEmail($this->physicianEmail);
        $this->assertGreaterThan(time() - 60, $lastEmail->created_at->timestamp);
        $body = json_decode($response->getBody(), true);
        $subject = $body['emailInvitation']['subject'];
        $physician = User::query()->orderByDesc(User::CREATED_AT)->first();
        $this->assertEquals($this->physicianEmail, $physician->email);
        $clientId = $physician->getPhysicianClientId();
        $this->assertStringContainsString('PHPUnit Test User wants', $subject);
        $usersAfter = User::pluck(User::FIELD_USER_EMAIL);
        $this->assertContains($this->physicianEmail, $usersAfter->all());
        $clientsAfter = OAClient::all();
        $collaboratorsAfter = Collaborator::all();
        $appsAfter = Application::all();
        $this->assertCount($clientsBefore + 1, $clientsAfter);
        $this->assertCount($collaboratorsBefore + 1, $collaboratorsAfter);
        $this->assertCount($appsBefore + 1, $appsAfter);
    }
    public function verifyReadOnlyAccess(): void{
        $physician = QMUser::findByEmail($this->physicianEmail);
        $this->setAuthenticatedUser($physician->getId());
        $users = $this->getUsersRequest();
        $this->assertCount(1, $users);
        foreach($users as $user){
            $this->setAuthenticatedUser(null);
            $token = $user->accessToken;
            $scope = $user->scope;
            $this->assertEquals("readmeasurements", $scope);
            QMBaseTestCase::setExpectedRequestException(InsufficientScopeException::class);
            $this->postAndGetDecodedBody('api/v3/measurements', [
                "variableName"         => "1521412625 Unique Test Variable",
                "value"                => 3,
                "startTimeEpoch"       => 1521413742,
                "unitAbbreviatedName"  => "/5",
                "variableCategoryName" => "Emotions",
                "latitude"             => null,
                "sourceName"           => "QuantiModo for web"
            ], false, 403, ['access_token' => $token]);
        }
    }
    public function testPatientInvitation(){
        $this->setAuthenticatedUser(1);
        $response = $this->postAndGetDecodedBody('/v1/shares/invitePatient', ['email' => 'test@quantimo.do']);
        $this->assertEquals(201, $response->status);
        $this->assertTrue($response->success);
    }
	/**
	 * @return ShareResponse
	 */
	public function assertAuthorizedClients(int $expectedIndividuals, int $expectedStudies): object {
		$this->setAuthenticatedUser(1);
		$response = $this->getAndDecodeBody('v1/shares');
		$this->assertCount($expectedStudies, $response->authorizedClients->studies);
		$individuals = $response->authorizedClients->individuals;
		$this->assertCount($expectedIndividuals, $individuals, "Sharing with these individuals: ".QMLog::print_r($individuals));
		return $response;
	}
}
